<?php
/**
 * Helper functions for loading mail templates
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


// Big mailer, no images, standard format  
function loopis_send_en_masse(string $mail_content, string $mail_subject){
    $batch_size = 50;
    $offset = 0;


    do {
        $users = get_users([
            'number'     => $batch_size,
            'offset'     => $offset,
            'orderby'    => 'ID',
            'order'      => 'ASC',
        ]);

        foreach ($users as $user) {
            $sent = wp_mail(
                $user->user_email,
                $mail_subject,
                $mail_content . loopis_mail_footer(),
                loopis_mail_headers()
            );

            if (!$sent) {
                error_log('WordPress could not send to subscriber ID ' . $user->id);
            }
        }

        usleep(100000);

        $offset += $batch_size;
    } while (count($users) === $batch_size);

}

// Test the weekly mail  
function loopis_send_en_masse_images_test(string $mail_content, string $mail_subject){
    $user = wp_get_current_user(); 
    $safe_mail_content = nl2br(esc_html($mail_content));
    $limit = 5;

    $base_mail =  '<div style="max-width: 500px; margin: 0 auto; padding: 5px; text-align: center; font-size: 16px;">
        <div style="margin: 10px;"><img src="https://loopis.app/wp-content/themes/loopis-theme/assets/img/LOOPIS_mail_header.png" alt="LOOPIS logotyp" width="240" /></div>
        <div id="message" style="white-space: pre-wrap;"><p style="margin: 5px">'.$safe_mail_content.'</p></div>
        </div>';

    $posts = get_posts([
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'tax_query'      => [[
                    'taxonomy' => 'admin-tag',
                    'field'    => 'slug',
                    'terms'    => 'mail_tips',
                ]],
            ]);
    
    shuffle($posts);
    $posts_slice =  array_slice(
        $posts,
        0,
        $limit);

    $mail = $base_mail . loopis_mail_tip_images($posts_slice)  . loopis_weekly_mail_footer();

    $sent = wp_mail(
        $user->user_email,
        $mail_subject,
        $mail,
        loopis_mail_headers()
    );

    if (!$sent) {
        error_log('WordPress could not send to subscriber ID ' . $user->id);
    }
    return $sent;
}


// Big mailer, with tip images, weekly mail format  
function loopis_send_en_masse_images(string $mail_content, string $mail_subject){
    $safe_mail_content = nl2br(esc_html($mail_content));

    $base_mail =  '<div style="max-width: 500px; margin: 0 auto; padding: 5px; text-align: center; font-size: 16px;">
        <div style="margin: 10px;"><img src="https://loopis.app/wp-content/themes/loopis-theme/assets/img/LOOPIS_mail_header.png" alt="LOOPIS logotyp" width="240" /></div>
        <div id="message" style="white-space: pre-wrap;"><p style="margin: 5px">'.$safe_mail_content.'</p></div>
        </div>';
    

    $batch_size = 50;
    $offset = 0;
    $limit = 5;

    $posts = get_posts([
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'tax_query'      => [[
                    'taxonomy' => 'admin-tag',
                    'field'    => 'slug',
                    'terms'    => 'mail_tips',
                ]],
            ]);
    

    $unsubscribed_ids = get_option('unsubscribed_mail_ids', []);
    $unsubscribed_ids = is_array($unsubscribed_ids)
        ? array_map('intval', $unsubscribed_ids)
        : [];

    do {
        shuffle($posts);
        $posts_slice =  array_slice(
            $posts,
            0,
            $limit);

        $mail = $base_mail . loopis_mail_tip_images($posts_slice)  . loopis_weekly_mail_footer();

        $users = get_users([
            'exclude'    => $unsubscribed_ids,
            'number'     => $batch_size,
            'offset'     => $offset,
            'orderby'    => 'ID',
            'order'      => 'ASC',
        ]);

        foreach ($users as $user) {
            $sent = wp_mail(
                $user->email,
                $mail_subject,
                $mail,
                loopis_mail_headers()
            );

            if (!$sent) {
                error_log('WordPress could not send to subscriber ID ' . $user->id);
            }
        }

        usleep(100000);

        $offset += $batch_size;
    } while (count($users) === $batch_size);
}

function loopis_mail_tip_images(array $posts_slice){
    $html = '<div style="max-width: 240px; margin: 0px auto; padding: 0px;"></div>
        <div style="margin: 0 0 20px 0; padding: 0px; background: #f5f5f5; max-width: 240px; margin-left: auto; margin-right: auto;">';

    foreach ($posts_slice as $post) {
        error_log($post->ID);
        $thumbnail = get_the_post_thumbnail(
            $post->ID,
            'medium',
            [
                'alt'   => get_the_title( $post->ID ),
                'style' => 'display:block;width:240px;height:240px;object-fit:cover;',
            ]
        );
        $name = esc_html(get_the_title($post->ID));
        $cat = array_values(get_the_category($post->ID));
        $cat_name = !empty($cat) ? esc_html($cat[0]->name) : '';

        $html .= '<div style="height: 240px; width: 240px;">'.$thumbnail.'</div>
        <div style="padding: 7px;">
        <p style="margin: 0 0 5px 0; font-size: 24px; color: #1e73be;">'.$name.'</p>
        <p style="margin: 0px; font-size: 15px; color: #999999;">'.$cat_name.'</p>

        </div>';
    }
    $html .= '</div>';
    return $html;
}


        