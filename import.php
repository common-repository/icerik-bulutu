<?php
global $wpdb;
$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'icerikbulutu_apikey';
$resultscontent = $wpdb->get_var( "SELECT apikey FROM $table_name");

?><div class="wrap"><div class="wrap-ib"><?php
	if(!empty($_POST['huy'])){
  		$aDoor = $_POST['huy'];
	}
  if(empty($aDoor)) 
  {
    _e( 'You have not selected any article', 'icerik-bulutu' );

  } 
  else 
  {
  	$N = count($aDoor);

    for($i=0; $i < $N; $i++)
    {
		$data_array =  array(
		  "Entity" => array(
		        "Id" => $aDoor[$i],
		        "Advert"=>array(
		            "Company"=>array(
		                "WordpressKey"=>$resultscontent
		            )
		        )

		  ),
		);

		$make_call = callAPI('POST', 'https://api.icerikbulutu.com/v2/word-press/article', json_encode($data_array));
		$icerik_content = json_decode($make_call,true);

		$options = get_option( 'icerik_bulutu_settings' );

		$no = $icerik_content['Entity']['Id'];
		
		$query = "SELECT * FROM {$wpdb->posts} WHERE ID = $no";
		$results = $wpdb->get_results($query);

		if($options['icerik_bulutu_author'] == null){
			$postauthor = 1;
		}
		else{
			$postauthor = $options['icerik_bulutu_author'];
		}

		if($options['icerik_bulutu_status'] == null){
			$poststatus = "draft";
		}
		else{
			$poststatus = $options['icerik_bulutu_status'];
		}
		echo "<ul>";
		if($results == null){
			$wpdb->insert("wp_posts", array(
				"ID" => $icerik_content['Entity']['Id'],
				"post_author" => $postauthor,
				"post_date" => $icerik_content['Entity']['CreatedOn'],
				"post_date_gmt" => $icerik_content['Entity']['CreatedOn'],
				"post_content" => $icerik_content['Entity']['ContentArticle'],
				"post_title" => $icerik_content['Entity']['ContentTitle'],
				"post_status" => $poststatus,
				"comment_status" => 'open',
				"ping_status" => 'open',
				"post_name" => strtolower(preg_replace('/\s+/', '-', $icerik_content['Entity']['ContentTitle'])),
				"post_modified" => $icerik_content['Entity']['CreatedOn'],
				"post_modified_gmt" => $icerik_content['Entity']['CreatedOn'],
				"guid" => get_site_url()."/?p=".$icerik_content['Entity']['Id'],
				"post_type" => 'post',
				"comment_count" => '0',
			));
			echo "<li>".$icerik_content['Entity']['ContentTitle']."".__( ' <strong style="color:green">article imported</strong>', 'icerik-bulutu' )."</li>";
		}
		else{
			echo "<li>".$icerik_content['Entity']['ContentTitle']."".__( ' <strong style="color:red">previously imported </strong>', 'icerik-bulutu' )."</li>";
		}
		echo "</ul>";
    }
    echo "<div class='notice notice-success is-dismissible import-done'>
            <p><strong>". __('Done', 'icerik-bulutu' ) ."</strong></p>
        </div>";
  }






?></div></div>

