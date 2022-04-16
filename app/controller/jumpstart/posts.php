<?php defined('ABSPATH') || exit('No direct script access allowed');

add_action('vverner/jumpstart/posts', function () {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $minimumPostsCount  = 3;
    $total              = 0;
    $uploadDir          = wp_upload_dir();
    $uploadPath         = $uploadDir['path'];

    while ($total < $minimumPostsCount) :
        $postId   = wp_insert_post([
            'post_type'     => 'post',
            'post_status'   => 'publish',
            'post_title'    => 'Demonstrativo 0' . $total,
            'post_content'  => '
                <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Nihil consequatur aspernatur sint, incidunt labore magni nesciunt modi assumenda veritatis deserunt neque ea. Natus, temporibus iste quia ex quod dolor doloremque?</p> 
                <p>Libero incidunt animi eaque ad deleniti sunt eligendi at, excepturi asperiores ex velit fuga. Iste nihil neque cum ea officia labore dignissimos repellendus, minima dolorum? Voluptatibus magni temporibus aspernatur labore?</p> 
                <p>Beatae, perspiciatis. Ab soluta facilis ad tempora consequuntur voluptatibus praesentium quasi nam doloribus adipisci reiciendis optio, ratione delectus commodi a repellendus eius. Dicta nisi suscipit sequi porro minima autem maiores!</p>'
        ]);

        $placeholder = VV_APP . '/assets/img/placeholder-' . $total . '.jpg';
        $name        = basename($placeholder);
        $file        = $uploadPath . '/' . $name;
        $fileType    = wp_check_filetype($name, null);

        copy($placeholder, $file);

        $imageId  = wp_insert_attachment([
            'post_mime_type' => $fileType['type'],
            'post_title'     => sanitize_file_name($name),
            'post_content'   => '',
            'post_status'    => 'inherit'
        ], $file, $postId);

        $meta = wp_generate_attachment_metadata($imageId, $file);
        wp_update_attachment_metadata($imageId, $meta);
        set_post_thumbnail($postId, $imageId);

        $total++;
    endwhile;
});
