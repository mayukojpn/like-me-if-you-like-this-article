<?php
/*
Plugin Name: Like me if you like this article
Description: This will recommend to like any Facebook page on the bottom of every single article.
Author: Mayuko Moriyama
Version: 0.1
Author URI: http://blog.mayuko.me
Plugin URI: https://github.com/mayukojpn/like-me-if-you-like-this-article
Text Domain: mamahack
*/

$mamahack = new FB_if_you_like();
$mamahack->register();

class FB_if_you_like {

	public function register()
	{
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}
	public function plugins_loaded()
	{
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), 21 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'script_loader_src', array( $this, 'async_script_loader_src' ), '', 2 );


	}
	public function wp_enqueue_scripts()
	{
		/*
		* If you want to style only your theme. You can stop style of this plugin.
		*
		* add_filter( 'mamahack_style', "__return_false" );
		*/
		$style = apply_filters( 'mamahack_style', plugins_url( 'css/mamahack.css', __FILE__ ) );
		if ( $style ) {
			wp_enqueue_style(
				'mamahack_style',
				$style
			);
		}

	}
	public function the_content( $contents )
	{
		if ( ! is_singular() ) {
			return $contents;
		}
		$like = '<div style="padding:10px 0px;"></div>';

		if ( get_option( 'mamahack_fb_account' ) )
		{
			$like .= '<div class="p-entry__push">';
			$like .= '<div class="p-entry__pushThumb" style="background-image: url(';
			if ( has_post_thumbnail( $post->ID ) )
			{
				$like .= wp_get_attachment_image_url( get_post_thumbnail_id($post->ID), 'medium' );
			}
			elseif ( has_site_icon() )
			{
				$like .= get_site_icon_url();
			}
			$like .= ')"></div>';
			$like .= '<div class="p-entry__pushLike">';
			$like .= '<p>'._('この記事が気に入ったら<br>いいね！しよう').'</p>';
			$like .= '<div class="p-entry__pushButton">';
			$like .= '<iframe src="https://www.facebook.com/plugins/like.php?href=https://www.facebook.com/';
			$like .=  esc_html( get_option('mamahack_fb_account') );
			$like .= '&send=false&layout=button_count&width=100&show_faces=false&action=like&colorscheme=light&font=arial&height=20" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>';
			$like .= '</div>';
			$like .= '<p class="p-entry__note">最新情報をお届けします</p>';
			$like .= '</div>';
			$like .= '</div>';
		}
		if( get_option( 'mamahack_tw_account' ) )
		{
			$like .= '<div class="p-entry__tw-follow">';
			$like .= '<div class="p-entry__tw-follow__cont">';
			$like .= '<p class="p-entry__tw-follow__item">';

			if( $mamahack_tw_message = esc_html( get_option( 'mamahack_tw_message ') ) )
			{
				$like .= $mamahack_tw_message;
			}
			else
			{
				$like .= 'Twitterでフォローしよう！';
			}
			$like .= '</p>';
			$like .= '<a href="https://twitter.com/'.esc_html( get_option('mamahack_tw_account') ).'" class="twitter-follow-button p-entry__tw-follow__item" data-show-count="false" data-size="large" data-show-screen-name="false">Follow @'.esc_html( get_option('mamahack_tw_account') ).'</a>';
			$like .= '</div>';
			$like .= '</div>';
		}

		return apply_filters( 'mamahack_the_content', $contents . $like, $like, $contents );
	}
	public function async_script_loader_src() {
		$html = "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>";
			echo $html;
		add_action( 'wp_footer', array( $this, 'wp_footer' ), 21 );
		return '';
	}
	public function wp_footer()
	{
		echo '<div id="fb-root"></div>';

	}

	public function mamahack_section_message() {

	}

	public function mamahack_fb_account() { ?>
	 	https://www.facebook.com/<input name="mamahack_fb_account" id="mamahack_fb_account" type="text" size="30" value="<?php
	 		echo esc_html( get_option('mamahack_fb_account') ); ?>">/
		<?php
	}

	public function mamahack_tw_message() { ?>
	 	<input name="mamahack_tw_message" id="mamahack_tw_message" type="text" size="30" value="<?php
	 		echo esc_html( get_option('mamahack_tw_message') ); ?>">
		<?php
	}

	public function mamahack_tw_account() { ?>
	 	@<input name="mamahack_tw_account" id="mamahack_tw_account" type="text" size="30" value="<?php
	 		echo esc_html( get_option('mamahack_tw_account') ); ?>">
		<?php
	}


	public function admin_init() {
		add_settings_section(
			'mamahack',
			_( '「この記事が良かったらいいね」の設定' ),
			array( $this, 'mamahack_section_message' ),
			'reading' );

	 	add_settings_field(
			'mamahack_fb_account',
			_( 'Facebook ページ名' ),
			array( $this, 'mamahack_fb_account' ),
			'reading',
			'mamahack');

		add_settings_field(
			'mamahack_tw_message',
			_( 'Twitter メッセージ' ),
			array( $this, 'mamahack_tw_message' ),
			'reading',
			'mamahack');

		add_settings_field(
			'mamahack_tw_account',
			_( 'Twitter アカウント' ),
			array( $this, 'mamahack_tw_account' ),
			'reading',
			'mamahack');


	 	register_setting('reading','mamahack_fb_account');
		register_setting('reading','mamahack_tw_message');
		register_setting('reading','mamahack_tw_account');
	 }

}
