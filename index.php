<?php
/**
 * Plugin Name: Craw Data
 * Description: Web Crawler is a software designed with the purpose to systematically browse websites on the World Wide Web, helping to gather information of those websites for search engines.
 * Author: Huynh Long
 * Version: 1.0.0
 * Text Domain: crawdata
 */
define( 'CRAW_DATA_PATH', plugin_dir_path( __FILE__ ) );
define( 'CRAW_DATA_URL', plugins_url('/', __FILE__ ) );
if( !class_exists('CRAWDATA_ADMIN') ){
    class CRAWDATA_ADMIN{
        function __construct(){
            add_action( 'admin_menu', array( $this, 'crawdataAddSidebar') );
            add_action( 'wp_ajax_crawDataAjax', [$this,'crawdataGetURL'] );
            add_action( 'wp_ajax_nopriv_crawDataAjax',  [$this,'crawdataGetURL'] );
            add_action( 'admin_footer', [$this,'crawdataScript'] );
            add_action( 'wp_ajax_addPostData', [$this,'crawdataSavePost'] );
            add_action( 'wp_ajax_nopriv_addPostData',  [$this,'crawdataSavePost'] );
            add_action( 'admin_head', [$this,'OtEnqueueScript'] );
        }
        function OtEnqueueScript(){
            wp_enqueue_style('craw-style', CRAW_DATA_URL . 'assets/css/style.css', array(), '1.0.0');
        }
        public function crawdataAddSidebar() {
            add_menu_page( esc_html__( 'OT Craw Data', 'crawdata' ), esc_html__( 'OT Craw Data', 'crawdata' ), 'manage_options', 'ot-page', array($this, 'crawdataPageContent'), 'dashicons-cloud', 300 );
        }
        public function crawdataPageContent() {
            require_once( CRAW_DATA_PATH.'/includes/craw.php' );
        }
        public function crawdataGetURL(){
            echo wp_remote_retrieve_body( wp_remote_get($_GET['url']) );
            die();
        }
        public function crawdataSavePostThumbnail( $imgUrl, $post_id){
            if(empty($imgUrl)) return;
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            $upload_dir = wp_upload_dir();
            $image_data = wp_remote_retrieve_body( wp_remote_get($imgUrl) );
            $filename = basename($imgUrl);
            if(wp_mkdir_p($upload_dir['path']))
                $file = $upload_dir['path'] . '/' . $filename;
            else
                $file = $upload_dir['basedir'] . '/' . $filename;
            file_put_contents($file, $image_data);
            $wp_filetype = wp_check_filetype($filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            set_post_thumbnail( $post_id, $attach_id );
        }
        public function crawdataSavePost(){
            global $post;
            $post_id = wp_insert_post( array(
                'post_title'    => sanitize_text_field( $_POST['title'] ),     
                'post_content'  => wp_filter_post_kses( isset( $_POST['content'] ) ? $_POST['content'] : '' ),
                'post_status'   => 'pending', 
                'post_type'     => sanitize_key( $_POST['post_type'] )
            ) );
            if (isset($_POST['image'])){
                $this->crawdataSavePostThumbnail(  sanitize_text_field( $_POST['image'] )  ,$post_id );
            }
            exit();
        }
        public function crawdataScript(){
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {

                    $(window).on('load resize', function(event) {
                        var mode  = screen.width;

                        if( mode >= 992 ){
                            console.log('992');
                            $('.form-craw .left, .form-craw .right').css('width', '50%');
                        }
                        if( 768 < mode < 992 ){
                            console.log('768 - 991');
                            $('.form-craw .left, .form-craw .right').css('width', '50%');
                        }
                        if( mode < 768 ){
                            console.log('768');

                            $('.form-craw .left, .form-craw .right').css('width', '100%');

                        }
                        // if( mode > 1024 ){
                        //     console.log('1024');
                        // }

                       
                    });

                    $(document).on('click', '#demo', function(event) {
                        event.preventDefault();
                        $(this).nextAll('.full-ovl').addClass('open');
                    });
                    $(document).on('click', '#closes', function(event) {
                        event.preventDefault();
                        $(this).closest('.full-ovl').removeClass('open');
                    });
                    // Show message
                    function showMess(mess){
                        $('#mess').append(mess);
                    }
                    $('.form-craw').submit(function(event) {
                        event.preventDefault();
                        var field_per_page = $('.field_per_page').val(),
                            field_url1 = $('.field_url1').val(),
                            field_item1 = $('.field_item1').val(),
                            field_not_item = $('.field_not_item').val(),
                            field_perlink = $('.field_perlink').val(),
                            field_title = $('.field_title').val(),
                            field_content = $('.field_content').val(),
                            field_pr = $('.field_pr').val(), // Action ?a=
                            field_fm = $('.field_fm').val(), // HTML
                            post_type = $('.post_type').val(); // Custom Post Type
                        // Page current
                        var currentPage = 1;
                        if (currentPage == 0){
                            currentPage = 1;
                        }
                        allItem  = [];
                        allImage  = [];
                        allTitle  = [];
                        savePageIndex = 0;
                        function startAPage(){
                            allItem  = [];
                            allImage  = [];
                            allTitle  = [];
                            savePageIndex = 0;
                            var page = field_pr+currentPage,
                                pa = $.trim(field_url1+page+field_fm);
                            $.ajax({
                                url : "<?php echo admin_url('admin-ajax.php');?>",
                                data : {
                                    url : pa,
                                    action : 'crawDataAjax'
                                },
                                type : "get",
                                dataType:"text",
                                beforeSend: function( xhr ) {
                                    $('.loading').addClass('active');
                                },                            
                                success : function(result){
                                    var html = $.parseHTML(result);
                                    var items = (field_not_item) ? $(html).find(field_item1).not( field_not_item ) : $(html).find(field_item1);
                                    for (var i = 0; i <= items.length; i++){
                                        var html = $.parseHTML(result);
                                        // Get Link
                                        var item = $(items[i]).find(field_perlink).attr('href');
                                        // Get thumbnail
                                        var imgSS = $(items[i]).find('img');
                                        if(imgSS.hasClass('lazy')){
                                            var image = $(items[i]).find('img').data('original');
                                        }else{
                                            var image = $(items[i]).find('img').attr('src');
                                        }
                                        // Get title
                                        var title = $(items[i]).find(field_title).text();
                                        allItem.push(item);
                                        allImage.push(image);
                                        allTitle.push(title);
                                    }
                                    // Start save post
                                    if (allItem.length > 0 && currentPage <= field_per_page){
                                        savePage();
                                    }
                                    else {
                                        showMess('<span style="color: #3c763d;background-color: #dff0d8;border-color: #d6e9c6;border-radius: 4px;padding: 15px;display:block;flex: 0 0 100%;-webkit-flex: 0 0 100%;">Success!</span>');
                                        $('.loading').removeClass('active');
                                    }
                                }
                            });
                        }
                        startAPage();

                        


                        var savePageIndex = 0;
                        function savePage(){
                            var url = allItem[savePageIndex],
                                img = allImage[savePageIndex],
                                title = allTitle[savePageIndex];
                            $.ajax({
                                url : "<?php echo admin_url('admin-ajax.php');?>",
                                data : {
                                    url : url,
                                    action : 'crawDataAjax'
                                },
                                type : "get",
                                dataType:"text",
                                success : function(result){
                                    var html = $.parseHTML(result),
                                        data = {
                                        title : title,
                                        image: img,
                                        content: $(html).find(field_content).html(),
                                        post_type: post_type,
                                        action : 'addPostData'
                                    };
                                    $.ajax({
                                        url : "<?php echo admin_url('admin-ajax.php');?>",
                                        data : data,
                                        type : "post",
                                        dataType:"text",
                                        success : function(result){
                                            var image = ( data.image ) ? '<img src="'+data.image+'"/>' : '';
                                            showMess('<span class="craw-item"><span>'+image+'</span><span>'+data.title+'</span></span>');
                                            var n = $( ".craw-item" ).length;
                                            $('.right .count').text(n);
                                            // Next post
                                            savePageIndex++;
                                            if (savePageIndex < allItem.length - 1){
                                                savePage();
                                            }
                                            else {
                                                currentPage++;
                                                startAPage();
                                            }
                                        }
                                    });
                                }
                            });
                        }
                        return false;
                    }); 
            });
        </script>
        <?php
        }
    }
    new CRAWDATA_ADMIN();
}