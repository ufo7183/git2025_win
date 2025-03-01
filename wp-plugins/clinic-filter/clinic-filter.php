<?php
/*
Plugin Name: Clinic Filter
Description: 使用 ACF 管理診所，自訂文章類型 + AJAX 篩選，電腦版(>700px)置中、手機版(<=700px)兩個下拉一行、搜尋欄一行、列表不併排
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

// 1. 註冊「診所」Post Type
if (!function_exists('clinic_filter_register_post_type')) {
    function clinic_filter_register_post_type() {
        register_post_type('clinic', array(
            'labels' => array(
                'name'               => '診所',
                'singular_name'      => '診所',
                'menu_name'          => '診所管理',
                'add_new'            => '新增診所',
                'add_new_item'       => '新增診所',
                'edit_item'          => '編輯診所',
                'new_item'           => '新診所',
                'view_item'          => '查看診所',
                'search_items'       => '搜尋診所',
                'not_found'          => '找不到診所',
                'not_found_in_trash' => '回收桶中沒有診所'
            ),
            'public'      => true,
            'has_archive' => true,
            'rewrite'     => array('slug' => 'clinic'),
            'supports'    => array('title','editor','thumbnail'),
        ));
    }
    add_action('init', 'clinic_filter_register_post_type');
}

// 2. 註冊「診所地區」taxonomy
if (!function_exists('clinic_filter_register_taxonomy')) {
    function clinic_filter_register_taxonomy() {
        register_taxonomy('clinic_location', array('clinic'), array(
            'hierarchical' => true,
            'labels'       => array(
                'name' => '地區',
                'singular_name' => '地區',
                'menu_name' => '地區',
                'all_items' => '所有地區',
                'parent_item' => '上層地區',
                'add_new_item' => '新增地區'
            ),
            'rewrite' => array('slug' => 'clinic-location')
        ));
    }
    add_action('init', 'clinic_filter_register_taxonomy');
}

// 3. 前端樣式 & JS
if (!function_exists('clinic_filter_enqueue_scripts')) {
    function clinic_filter_enqueue_scripts() {
        wp_enqueue_script('jquery');

        // 註冊空白CSS
        wp_register_style('clinic-filter-style', false);
        wp_enqueue_style('clinic-filter-style');

        // CSS：手動inline
        wp_add_inline_style('clinic-filter-style', '
/* =========== 全域容器 =========== */
.myplugin-wrapper {
  /* 讓它能置中 or 100% 視情況 */
  width: 100%;
  margin: 0 auto;
  box-sizing: border-box;
  text-align: center; /* 預設標籤置中 */
}

/* 外層 .clinic-filter-container：上方篩選區塊 */
.myplugin-wrapper .clinic-filter-container {
  margin-bottom: 30px;
}

/* 
   大螢幕 (>700px)：
     - 兩個下拉+搜尋欄並排 
     - 整體可固定1140px寬（或自行調整）
*/
@media (min-width: 700px) {
  .myplugin-wrapper .clinic-filter-row {
    display: flex;
    align-items: center;
    justify-content: center; /* 置中 */
    gap: 10px;
    width: 680px;  
    margin: 0 auto; /* 水平置中 */
  }
}

/*
   小螢幕 (<=700px)：
     - 兩個下拉在同一行
     - 搜尋欄自己一行 
*/
@media (max-width: 700px) {
  .myplugin-wrapper .clinic-filter-row {
    width: 100%;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap; /* 容許換行 */
    gap: 10px;
    justify-content: center;
  }
}

/* ========== 下拉選單樣式 ========== */
.myplugin-wrapper .clinic-filter-field select {
  display: block;
  font-size: 16px;
  line-height: 25.6px;
  padding: 5px 35px 5px 10px;
  border: none !important;
  border-radius: 8px;
  background-color: #fff;
  box-sizing: border-box;
  width: 100%;
}

/* 大螢幕：讓 city & area 合計跟搜尋欄位並排 */
@media (min-width: 700px) {
  .myplugin-wrapper .city, .myplugin-wrapper .area {
    width: 200px; /* 兩個下拉分別200px寬，可自行調 */
  }
}

/* 小螢幕：兩個下拉在同一行 => 各50% */
@media (max-width: 700px) {
  .myplugin-wrapper .city, .myplugin-wrapper .area {
    width: calc(50% - 10px); /* gap=10px, 左右各5px */
  }
}

/* ========== 搜尋區塊(搜尋欄+按鈕) ========== */
.myplugin-wrapper .search-box {
  width: 100%; /* 小螢幕一行佔滿 */
}
@media (min-width: 700px) {
  .myplugin-wrapper .search-box {
    flex: 1; /* 大螢幕撐剩餘空間 */
    max-width: 400px; /* 也可自行定義，讓兩下拉+搜尋合計1140 */
  }
}

/* 膠囊 */
.myplugin-wrapper .ser-box {
  display: inline-flex;
  align-items: center;
  width: 100%;
  border: 1px solid #ddd;
  border-radius: 40px;
  overflow: hidden;
  background: #fff;
  box-sizing: border-box;
}

/* 放大鏡圖示 */
.myplugin-wrapper .search-icon {
  font-size: 18px;
  color: #666;
  margin-left: 10px;
  margin-right: 8px;
}

/* 搜尋輸入 */
.myplugin-wrapper .ser-box input[type="text"] {
  border: none;
  outline: none;
  flex: 1;
  font-size: 16px;
  line-height: 25.6px;
  background: transparent;
  padding: 5px 10px;
}

/* 按鈕 */
.myplugin-wrapper .ser-box .button {
  background: #033473;
  color: #fff;
  border: none;
  padding: 8px 25px;
  font-size: 18px;
  line-height: 28.8px;
  border-radius: 40px;
  cursor: pointer;
  white-space: nowrap;
  z-index: 10;

}

/* =========== 列表區 =========== */
.myplugin-wrapper .clinic-list-container {
  width: 1140px;    /* 電腦版寬1140 */
  max-width: 100%;  /* 手機版滿版 */
  margin: 0 auto;   /* 置中 */
  text-align: left; /* 內容置左 */
}

.myplugin-wrapper .clinic-item {
  display: flex; 
  flex-wrap: wrap; 
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e5e5e5;
}

/* 小螢幕：診所列表不要併排 => 改每則100% */
@media (max-width: 700px) {
  .myplugin-wrapper .clinic-item {
    display: block; /* 每則獨立佔一行 */
  }
}

.myplugin-wrapper .clinic-title {
  flex: 0 0 40%;
  font-size: 18px;
  line-height: 28.8px;
  transition: color 0.3s;
  margin-bottom:0;
}
.myplugin-wrapper .clinic-title a {
  color: #333;
  text-decoration: none;
}
.myplugin-wrapper .clinic-title a:hover {
  color: #1D6579;
}
.myplugin-wrapper .clinic-address {
  flex: 0 0 40%;
  font-size: 18px;
  line-height: 28.8px;
}
.myplugin-wrapper .clinic-address a {
  color: #2b6cb0;
}
.myplugin-wrapper .clinic-phone a {
  color: #2b6cb0;
}
.myplugin-wrapper .clinic-phone {
  flex: 0 0 20%;
  font-size: 18px;
  line-height: 28.8px;
}
.myplugin-wrapper .no-results {
  padding: 30px;
  text-align: center;
  font-size: 18px;
  background: #f9f9f9;
  border-radius: 4px;
}
        ');

        // JS
        wp_register_script('clinic-filter-script','');
        wp_enqueue_script('clinic-filter-script');

        $inline_js = '
        jQuery(document).ready(function($){
            function filterClinics(city_id, area_id, keyword){
                $.ajax({
                    url: clinicAjax.ajaxurl,
                    method: "POST",
                    data: {
                        action: "clinic_filter",
                        security: clinicAjax.nonce,
                        city_id: city_id,
                        area_id: area_id,
                        keyword: keyword
                    },
                    beforeSend: function(){
                        $("#clinic-list-container").addClass("loading");
                    },
                    success: function(res){
                        $("#clinic-list-container").removeClass("loading").html(res);
                    }
                });
            }

            // 縣市
            $("#clinic_city").on("change", function(){
                var city_id = $(this).val() || "";
                $("#clinic_area").html("<option value=\'\'>選擇區域</option>").prop("disabled", true);

                // 篩選
                filterClinics(city_id, "", $("#clinic_keyword").val());
                if(!city_id) return;

                // 抓子區域
                $.ajax({
                    url: clinicAjax.ajaxurl,
                    method: "POST",
                    data: {
                        action: "clinic_filter_get_districts",
                        security: clinicAjax.nonce,
                        city_id: city_id
                    },
                    success: function(response){
                        if(response.success && response.data.length > 0){
                            $("#clinic_area").prop("disabled", false);
                            $.each(response.data, function(i, item){
                                $("#clinic_area").append("<option value=\'"+item.term_id+"\'>"+item.name+"</option>");
                            });
                        } else {
                            $("#clinic_area").prop("disabled", true);
                        }
                    }
                });
            });

            // 區域
            $("#clinic_area").on("change", function(){
                filterClinics($("#clinic_city").val(), $(this).val(), $("#clinic_keyword").val());
            });

            // 搜尋
            $("#clinic-filter-submit").on("click", function(e){
                e.preventDefault();
                filterClinics($("#clinic_city").val(), $("#clinic_area").val(), $("#clinic_keyword").val());
            });
        });
        ';
        wp_add_inline_script('clinic-filter-script', $inline_js);

        wp_localize_script('clinic-filter-script', 'clinicAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('clinic_filter_nonce')
        ));
    }
    // priority=999 -> 在其他外掛後
    add_action('wp_enqueue_scripts', 'clinic_filter_enqueue_scripts', 999);
}

// 4. 短代碼：搜尋表單
if (!function_exists('clinic_search_bar_shortcode')) {
    function clinic_search_bar_shortcode($atts) {
        $cities = get_terms(array(
            'taxonomy'   => 'clinic_location',
            'parent'     => 0,
            'hide_empty' => false
        ));
        ob_start();
        ?>
<div class="myplugin-wrapper">
  <div class="clinic-filter-container">
    <form id="clinic-filter-form">
      <div class="clinic-filter-row">
        <!-- 縣市 -->
        <div class="clinic-filter-field city">
          <select id="clinic_city" name="clinic_city">
            <option value="">選擇縣市</option>
            <?php if ($cities && !is_wp_error($cities)) : ?>
              <?php foreach($cities as $city) : ?>
                <option value="<?php echo esc_attr($city->term_id); ?>">
                  <?php echo esc_html($city->name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>
        <!-- 區域 -->
        <div class="clinic-filter-field area">
          <select id="clinic_area" name="clinic_area" disabled>
            <option value="">選擇區域</option>
          </select>
        </div>
        <!-- 搜尋欄 -->
        <div class="search-box">
          <div class="ser-box">
            <i class="fa fa-search search-icon"></i>
            <input type="text" id="clinic_keyword" placeholder="搜尋診所名稱" />
            <button type="submit" id="clinic-filter-submit" class="button">搜尋</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('clinic_search_bar', 'clinic_search_bar_shortcode');
}

// 5. 短代碼：診所列表
if (!function_exists('clinic_list_shortcode')) {
    function clinic_list_shortcode($atts) {
        ob_start();
        ?>
<div class="myplugin-wrapper">
  <div id="clinic-list-container" class="clinic-list-container">
    <?php
    echo clinic_filter_generate_list(); // 預設顯示
    ?>
  </div>
</div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('clinic_list', 'clinic_list_shortcode');
}

// 6. 產生列表 HTML
if (!function_exists('clinic_filter_generate_list')) {
    function clinic_filter_generate_list($city_id = 0, $area_id = 0, $keyword = '', $paged = 1) {
        $args = array(
            'post_type'      => 'clinic',
            'posts_per_page' => 10,
            'paged'          => $paged
        );

        $tax_query = array();
        if (!empty($area_id)) {
            $tax_query[] = array(
                'taxonomy' => 'clinic_location',
                'field'    => 'term_id',
                'terms'    => $area_id
            );
        } elseif (!empty($city_id)) {
            $child_terms = get_terms(array(
                'taxonomy'  => 'clinic_location',
                'parent'    => $city_id,
                'fields'    => 'ids',
                'hide_empty'=> false
            ));
            if (!is_wp_error($child_terms) && !empty($child_terms)) {
                $tax_query[] = array(
                    'taxonomy' => 'clinic_location',
                    'field'    => 'term_id',
                    'terms'    => $child_terms
                );
            }
        }
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        // 關鍵字
        if (!empty($keyword)) {
            $args['s'] = sanitize_text_field($keyword);
        }

        $query = new WP_Query($args);
        if (!$query->have_posts()) {
            return "<div class='no-results'>請重新搜尋</div>";
        }

        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            // ACF
            $address      = get_field('clinic_address') ?: '';
            $address_url  = get_field('clinic_address_url') ?: '';
            $phone        = get_field('clinic_phone') ?: '';
            $phone_url    = get_field('clinic_phone_url') ?: '';
            ?>
            <div class="clinic-item">
                <h3 class="clinic-title">
                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                <div class="clinic-address">
                    <?php if($address_url): ?>
                        <a href="<?php echo esc_url($address_url); ?>" target="_blank">
                            <?php echo esc_html($address); ?>
                        </a>
                    <?php else: ?>
                        <?php echo esc_html($address); ?>
                    <?php endif; ?>
                </div>
                <div class="clinic-phone">
                    <?php if($phone_url): ?>
                        <a href="<?php echo esc_url($phone_url); ?>">
                            <?php echo esc_html($phone); ?>
                        </a>
                    <?php else: ?>
                        <?php echo esc_html($phone); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        wp_reset_postdata();

        // 分頁
        $output = ob_get_clean();
        $output .= '<div class="clinic-pagination">';
        $output .= paginate_links(array(
            'base'      => '%_%',
            'format'    => '?paged=%#%',
            'current'   => $paged,
            'total'     => $query->max_num_pages,
            'prev_text' => '« 上一頁',
            'next_text' => '下一頁 »',
        ));
        $output .= '</div>';

        return $output;
    }
}

// 7. AJAX：取得子區域
if (!function_exists('clinic_filter_get_districts_ajax')) {
    function clinic_filter_get_districts_ajax() {
        check_ajax_referer('clinic_filter_nonce','security');
        $city_id = isset($_POST['city_id']) ? intval($_POST['city_id']) : 0;
        $data = array();

        if ($city_id > 0) {
            $terms = get_terms(array(
                'taxonomy'   => 'clinic_location',
                'parent'     => $city_id,
                'hide_empty' => false
            ));
            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $t) {
                    $data[] = array(
                        'term_id' => $t->term_id,
                        'name'    => $t->name
                    );
                }
            }
        }
        wp_send_json_success($data);
        wp_die();
    }
    add_action('wp_ajax_clinic_filter_get_districts','clinic_filter_get_districts_ajax');
    add_action('wp_ajax_nopriv_clinic_filter_get_districts','clinic_filter_get_districts_ajax');
}

// 8. AJAX：篩選
if (!function_exists('clinic_filter_ajax_search')) {
    function clinic_filter_ajax_search() {
        check_ajax_referer('clinic_filter_nonce','security');
        $city_id = isset($_POST['city_id']) ? intval($_POST['city_id']) : 0;
        $area_id = isset($_POST['area_id']) ? intval($_POST['area_id']) : 0;
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $paged   = 1;

        echo clinic_filter_generate_list($city_id, $area_id, $keyword, $paged);
        wp_die();
    }
    add_action('wp_ajax_clinic_filter','clinic_filter_ajax_search');
    add_action('wp_ajax_nopriv_clinic_filter','clinic_filter_ajax_search');
}
