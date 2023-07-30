<?php
/*
Plugin Name: DivinationPress
Plugin URI: https://basekix.com
Description: Adds a shortcode to display three random image links that lead to random posts from a specific category.
Version: 1.0.0
Author: Kasiri
Author URI: https://basekix.com
License: GPL2
*/

// プラグインのメニューページを追加するアクションフック
add_action('admin_menu', 'divinationpress_add_menu');

function divinationpress_add_menu() {
    // メニューページを追加
    add_menu_page(
        'DivinationPress',      // ページのタイトル
        'DivinationPress',      // メニューのテキスト
        'manage_options',       // 必要な権限
        'divinationpress',      // メニュースラッグ（一意のID）
        'divinationpress_page', // メニューページのコールバック関数
        'dashicons-visibility', // メニューアイコン（ここではアイコンは目のアイコンを指定）
        26                      // メニューの表示位置
    );
}

// メニューページのコールバック関数
function divinationpress_page() {
    // カテゴリ設定の処理
    if (isset($_POST['divinationpress_category'])) {
        update_option('divinationpress_category', $_POST['divinationpress_category']);
        echo '<div class="notice notice-success"><p>カテゴリ設定を保存しました。</p></div>';
    }

    // 画像設定の処理
    if (isset($_POST['divinationpress_image'])) {
        update_option('divinationpress_image', $_POST['divinationpress_image']);
        echo '<div class="notice notice-success"><p>画像を保存しました。</p></div>';
    }

    // 現在のカテゴリ設定を取得
    $current_category = get_option('divinationpress_category');
    // 現在の画像設定を取得
    $current_image = get_option('divinationpress_image');

    // メディアアップローダーのスクリプトを読み込む
    wp_enqueue_media();
    ?>
    <style>
        .divinationpress-image-preview {
            max-width: 300px;
        }
    </style>
    <?php
    // メニューページのコンテンツをここに記述
    echo '<h2>DivinationPress Settings</h2>';
    echo '<form method="post">';
    echo '<label for="divinationpress_category">表示させるカテゴリ:</label>';
    echo '<br>';
    // カテゴリをドロップダウンで表示
    wp_dropdown_categories(array(
        'show_option_all' => '全てのカテゴリ',
        'hide_empty' => 0,
        'name' => 'divinationpress_category',
        'selected' => $current_category,
    ));
    echo '<br>';
    echo '<input type="submit" value="保存">';
    echo '</form>';

    echo '<form method="post" style="margin-top: 20px;">';
    echo '<label for="divinationpress_image">設定する画像:</label>';
    echo '<input type="text" id="divinationpress_image" name="divinationpress_image" value="' . esc_attr($current_image) . '">';
    echo '<input type="button" class="button button-primary" value="画像を選択" onclick="selectImage();">';
    echo '<input type="submit" value="保存">';
    echo '</form>';

    // 保存されている画像を表示する
    if (!empty($current_image)) {
        echo '<h3>設定されている画像</h3>';
        echo '<img class="divinationpress-image-preview" src="' . esc_attr($current_image) . '"><br>';
    }

    // 最下部にリンクと画像を表示
    echo '<hr>';
    echo '<a href="https://basekix.com" target="_blank"><img src="' . plugin_dir_url(__FILE__) . 'assets/img/bgt.png" alt="Basekix" /></a>';

    ?>
    <script type="text/javascript">
        // メディアライブラリを開く関数
        function selectImage() {
            var customUploader = wp.media({
                title: '画像を選択',
                button: {
                    text: '選択'
                },
                multiple: false // 1つだけ選択できるようにする
            });

            customUploader.on('select', function() {
                var attachment = customUploader.state().get('selection').first().toJSON();
                document.getElementById('divinationpress_image').value = attachment.url;
            });

            customUploader.open();
        }
    </script>
    <?php
}

// ショートコードの定義
function divinationpress_shortcode() {
    // 現在の画像設定を取得
    $current_image = get_option('divinationpress_image');

    // ランダムなカテゴリIDを取得
    $categories = get_categories();
    $random_category_id = $categories[array_rand($categories)]->term_id;

    // カテゴリ内のランダムな記事を3つ取得する
    $args = array(
        'post_type' => 'post',
        'cat' => $random_category_id,
        'orderby' => 'rand',
        'posts_per_page' => 3,
    );

    $query = new WP_Query($args);

    // 記事リストを表示する処理（ここでは画像リンクを表示）
    if ($query->have_posts()) {
        echo '<div style="display: flex; align-items: center;">';
        while ($query->have_posts()) {
            $query->the_post();
            $post_link = get_permalink();

            echo '<div style="flex: 1; padding: 5px;">';
            if (!empty($current_image)) {
                // ショートコードの属性に指定された画像があれば画像リンクとして表示
                echo '<a href="' . $post_link . '"><img src="' . $current_image . '" style="width: 100%; max-width: 300px;"></a><br>';
            }
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '該当する記事がありません。';
    }

    wp_reset_postdata();
}

add_shortcode('divinationpress', 'divinationpress_shortcode');
