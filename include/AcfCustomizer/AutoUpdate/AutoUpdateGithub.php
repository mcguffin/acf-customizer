<?php

namespace AcfCustomizer\AutoUpdate;

if ( ! defined('ABSPATH') ) {
	die('FU!');
}

use AcfCustomizer\Core;

class AutoUpdateGithub extends AutoUpdate {

	private $github_repo = null;

	/**
	 *	@inheritdoc
	 */
	public function get_remote_release_info() {
		if ( $release_info_url = $this->get_release_info_url() ) {

			$response = wp_remote_get( $release_info_url, array() );

			if ( ! is_wp_error( $response ) ) {

				$release_info = json_decode( wp_remote_retrieve_body( $response ) );

				if ( ! is_object( $release_info ) || ! isset( $release_info->tag_name ) ) {
					return false;
				}

				return $this->extract_info( $release_info->body, array(
						'tested'		=> 'Tested up to',
						'requires_php'	=> 'Requires PHP',
						'requires'		=> 'Requires at least'
					) ) + array(
						'id'			=> sprintf( 'github.com/%s', $this->get_github_repo() ),
						'version_tag'	=> $release_info->tag_name,
						'version'		=> preg_replace( '/^([^0-9]+)/ims', '', $release_info->tag_name ),
						'download_link'	=> $release_info->zipball_url,
						'last_updated'	=> $release_info->published_at,
						'notes'			=> $release_info->body,
					);

			}
		}

		return false;
	}


	/**
	 *	@inheritdoc
	 */
	protected function get_plugin_sections() {
		$repo = $this->get_github_repo();

		$sections = array();

		$release_info = $this->get_release_info();

		// get plain github readme
		$readme_url = sprintf('https://raw.githubusercontent.com/%s/%s/README.md', $repo, $release_info['version_tag'] );

		$response = wp_remote_get( $readme_url );
		$readme = wp_remote_retrieve_body($response);

		// parse readme github mardown
		$response = wp_remote_post('https://api.github.com/markdown/raw', array(
			'headers'	=> array(
				'Content-Type' => 'text/plain',
			),
			'body' => $readme,
		));

		if ( ! is_wp_error( $response ) ) {
			$sections[ __('Description','gitupdate-test') ] = wp_remote_retrieve_body($response);
		}


		// parse release info github mardown
		$response = wp_remote_post('https://api.github.com/markdown/raw', array(
			'headers'	=> array(
				'Content-Type' => 'text/plain',
			),
			'body' => $release_info['notes'],
		));

		if ( ! is_wp_error( $response ) ) {
			$sections[ __('Notes','gitupdate-test') ] = wp_remote_retrieve_body( $response );
		}

		return $sections;
	}
	/**
	 *	@return	string	github-owner/github-repo
	 */
	private function get_github_repo() {
		if ( is_null( $this->github_repo ) ) {
			$this->github_repo = false;
			$data = get_file_data( ACF-CUSTOMIZER_FILE, array('GithubRepo'=>'Github Repository') );
			if ( ! empty( $data['GithubRepo'] ) ) {
				$this->github_repo = $data['GithubRepo'];
			}
		}
		return $this->github_repo;

	}

	/**
	 *	@return	string	github api url
	 */
	private function get_release_info_url() {
		$url = false;
		if ( $repo = $this->get_github_repo() ) {
			$url = sprintf('https://api.github.com/repos/%s/releases/latest', $repo );
		}
		return $url;
	}

}
