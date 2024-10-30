<div class="wp-craw">
	<div class="field-craw">
		<div class="demo-video">
			<a href="javascript:;" id="demo"><?php esc_html_e( 'Demo', 'crawdata' ) ?></a>
			<!-- <a href="javascript:;" id="stop"><?php esc_html_e( 'Stop', 'crawdata' ) ?></a> -->
			<div class="full-ovl">
				<div class="video-content">
					<video width="100%" controls>
						<source src="<?php echo esc_url( CRAW_DATA_URL. 'demo/demo.webm' ) ?>" type="video/webm">
							<?php esc_html_e( 'Your browser does not support HTML5 video.' , 'crawdata' ) ?>
						</video>
						<a href="javascript:;" id="closes">X</a>
					</div>
				</div>
			</div>
			<form method="post" action="" class="form-craw">
				<?php  
				$post_types = get_post_types( [], 'objects' );
				?>
				<div class="left">
					<div class="loading">
						<img src="<?php echo esc_url( CRAW_DATA_URL .'assets/images/loading.gif' ); ?>" alt="loading">
					</div>
					<div class="field_item" title="Limit per page">
						<label><?php esc_html_e( 'Per page', 'crawdata' ) ?></label>
						<input type="number" name="field_per_page" class="field_per_page" value="1" />
					</div>
					<div class="field_item">
						<label><?php esc_html_e( 'Website', 'crawdata' ) ?></label>
						<input type="url" name="field_url1" class="field_url1" placeholder="abc.com/blog" required="required" />
					</div>
					<div class="field_item">
						<label><?php esc_html_e( 'Param', 'crawdata' ) ?></label>
						<input type="text" name="field_pr" class="field_pr" placeholder="?page=" required="required" />
						<p>EX: <strong style="color:red">?page=</strong> , <strong style="color:red">/page/</strong> , <strong style="color:red"> ... </strong></p>
					</div>
					<div class="field_item">
						<label><?php esc_html_e( 'Format', 'crawdata' ) ?></label>
						<input type="text" name="field_fm" class="field_fm" placeholder=".html" />
					</div>
					<div class="field_item">
						<label><?php esc_html_e( 'Item', 'crawdata' ) ?></label>
						<input type="text" name="field_item1" class="field_item1" placeholder=".list-item .item" required="required" />
					</div>
					<div class="field_item">
						<label><?php esc_html_e( 'Not Item', 'crawdata' ) ?></label>
						<input type="text" name="field_not_item" class="field_not_item" placeholder="" />
					</div>
					<div class="field_item">
						<label><?php esc_html_e( 'To link', 'crawdata' ) ?></label>
						<input type="text" name="field_perlink" class="field_perlink" placeholder="a.link" required="required"/>
					</div>
					<div class="field_item">
						<label><?php esc_html_e( 'Title', 'crawdata' ) ?></label>
						<input type="text" name="field_title" class="field_title" placeholder="a.title"  required="required"/>
					</div>
					<div class="field_item">
						<label><?php esc_html_e( 'Content', 'crawdata' ) ?></label>
						<input type="text" name="field_content" class="field_content" />
					</div>
					<div class="field_item">
						<label><?php esc_html_e( 'Post Type', 'crawdata' ) ?></label>
						<?php  
						if(count($post_types) > 0){ ?>
							<select name="post_type" class="post_type">
								<?php foreach ($post_types as $key => $post_type) {
									$exclude = array( 
										'wpcf7_contact_form', 
										'wp_block', 
										'user_request', 
										'oembed_cache', 
										'customize_changeset', 
										'custom_css', 
										'nav_menu_item', 
										'revision', 
										'attachment', 
										'page', 
										'elementor_library' 
									);
									if( TRUE === in_array( $post_type->name, $exclude ) ) continue;
									echo '<option value="'.$post_type->name.'">'.$post_type->labels->singular_name.'</option>';
								} ?>
							</select>
						<?php }
						?>
					</div>
					<div class="btn-save">
						<button type="submit" class="btn-craw"><?php esc_html_e( 'Start', 'crawdata' ) ?></button>
					</div>
				</div>
				<div class="right">
					<h2><?php esc_html_e( 'Totals: ', 'crawdata' ) ?> <span class="count">0</span></h2>
					<div id="mess"></div>
				</div>
			</form>
			<!-- show content -->
		</div>
	</div>