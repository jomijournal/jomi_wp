<?php

/**
 * @group admin
 */
class Tests_Admin_includesPost extends WP_UnitTestCase {

	function tearDown() {
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	function test__wp_translate_postdata_cap_checks_contributor() {
		$contributor_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
		$editor_id = $this->factory->user->create( array( 'role' => 'editor' ) );

		wp_set_current_user( $contributor_id );

		// Create New Draft Post
		$_post_data = array();
		$_post_data['post_author'] = $contributor_id;
		$_post_data['post_type'] = 'post';
		$_post_data['saveasdraft'] = true;

		$_results = _wp_translate_postdata( false, $_post_data );
		$this->assertNotInstanceOf( 'WP_Error', $_results );
		$this->assertEquals( $_post_data['post_author'], $_results['post_author'] );
		$this->assertEquals( 'draft', $_results['post_status'] );

		// Submit Post for Approval
		$_post_data = array();
		$_post_data['post_author'] = $contributor_id;
		$_post_data['post_type'] = 'post';
		$_post_data['publish'] = true;

		$_results = _wp_translate_postdata( false, $_post_data );
		$this->assertNotInstanceOf( 'WP_Error', $_results );
		$this->assertEquals( $_post_data['post_author'], $_results['post_author'] );
		$this->assertEquals( 'pending', $_results['post_status'] );

		// Create New Draft Post for another user
		$_post_data = array();
		$_post_data['post_author'] = $editor_id;
		$_post_data['post_type'] = 'post';
		$_post_data['saveasdraft'] = true;

		$_results = _wp_translate_postdata( false, $_post_data );
		$this->assertInstanceOf( 'WP_Error', $_results );
		$this->assertEquals( 'edit_others_posts', $_results->get_error_code() );
		$this->assertEquals( 'You are not allowed to create posts as this user.', $_results->get_error_message() );

		// Edit Draft Post for another user
		$_post_data = array();
		$_post_data['post_ID'] = $this->factory->post->create( array( 'post_author' => $editor_id ) );
		$_post_data['post_author'] = $editor_id;
		$_post_data['post_type'] = 'post';
		$_post_data['post_status'] = 'draft';
		$_post_data['saveasdraft'] = true;

		$_results = _wp_translate_postdata( true, $_post_data );
		$this->assertInstanceOf( 'WP_Error', $_results );
		$this->assertEquals( 'edit_others_posts', $_results->get_error_code() );
		$this->assertEquals( 'You are not allowed to edit posts as this user.', $_results->get_error_message() );
	}

	function test__wp_translate_postdata_cap_checks_editor() {
		$contributor_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
		$editor_id = $this->factory->user->create( array( 'role' => 'editor' ) );

		wp_set_current_user( $editor_id );

		// Create New Draft Post
		$_post_data = array();
		$_post_data['post_author'] = $editor_id;
		$_post_data['post_type'] = 'post';
		$_post_data['saveasdraft'] = true;

		$_results = _wp_translate_postdata( false, $_post_data );
		$this->assertNotInstanceOf( 'WP_Error', $_results );
		$this->assertEquals( $_post_data['post_author'], $_results['post_author'] );
		$this->assertEquals( 'draft', $_results['post_status'] );

		// Publish Post
		$_post_data = array();
		$_post_data['post_author'] = $editor_id;
		$_post_data['post_type'] = 'post';
		$_post_data['publish'] = true;

		$_results = _wp_translate_postdata( false, $_post_data );
		$this->assertNotInstanceOf( 'WP_Error', $_results );
		$this->assertEquals( $_post_data['post_author'], $_results['post_author'] );
		$this->assertEquals( 'publish', $_results['post_status'] );

		// Create New Draft Post for another user
		$_post_data = array();
		$_post_data['post_author'] = $contributor_id;
		$_post_data['post_type'] = 'post';
		$_post_data['saveasdraft'] = true;

		$_results = _wp_translate_postdata( false, $_post_data );
		$this->assertNotInstanceOf( 'WP_Error', $_results );
		$this->assertEquals( $_post_data['post_author'], $_results['post_author'] );
		$this->assertEquals( 'draft', $_results['post_status'] );

		// Edit Draft Post for another user
		$_post_data = array();
		$_post_data['post_ID'] = $this->factory->post->create( array( 'post_author' => $contributor_id ) );
		$_post_data['post_author'] = $contributor_id;
		$_post_data['post_type'] = 'post';
		$_post_data['post_status'] = 'draft';
		$_post_data['saveasdraft'] = true;

		$_results = _wp_translate_postdata( true, $_post_data );
		$this->assertNotInstanceOf( 'WP_Error', $_results );
		$this->assertEquals( $_post_data['post_author'], $_results['post_author'] );
		$this->assertEquals( 'draft', $_results['post_status'] );
	}

	/**
	 * edit_post() should convert an existing auto-draft to a draft.
	 *
	 * @ticket 25272
	 */
	function test_edit_post_auto_draft() {
		$user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );
		$post = $this->factory->post->create_and_get( array( 'post_status' => 'auto-draft' ) );
		$this->assertEquals( 'auto-draft', $post->post_status );
		$post_data = array(
			'post_title' => 'Post title',
			'content' => 'Post content',
			'post_type' => 'post',
			'post_ID' => $post->ID,
		);
		edit_post( $post_data );
		$this->assertEquals( 'draft', get_post( $post->ID )->post_status );
	}

	/**
	 * @ticket 27792
	 */
	function test_bulk_edit_posts_stomping() {
		$admin = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$users = $this->factory->user->create_many( 2, array( 'role' => 'author' ) );
		wp_set_current_user( $admin );

		$post1 = $this->factory->post->create( array(
			'post_author'    => $users[0],
			'comment_status' => 'open',
			'ping_status'    => 'open',
			'post_status'    => 'publish',
		) );

		$post2 = $this->factory->post->create( array(
			'post_author'    => $users[1],
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_status'    => 'draft',
		) );

		$request = array(
			'post_type'        => 'post',
			'post_author'      => -1,
			'ping_status'      => -1,
			'comment_status'   => -1,
			'_status'          => -1,
			'post'             => array( $post1, $post2 ),
		);

		$done = bulk_edit_posts( $request );

		$post = get_post( $post2 );

		// Check that the first post's values don't stomp the second post.
		$this->assertEquals( 'draft', $post->post_status );
		$this->assertEquals( $users[1], $post->post_author );
		$this->assertEquals( 'closed', $post->comment_status );
		$this->assertEquals( 'closed', $post->ping_status );
	}

}