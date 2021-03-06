<?php
function getHTML(){
	
	return '<!DOCTYPE HTML><html><head><title>Pinoy Destination</title><style> html,body{ margin:0; padding:0; }</style> </head> <body> 
	<script type="text/javascript"><!--
	google_ad_client = "ca-pub-0908617034545427";
	/* BoxAd */
	google_ad_slot = "3794932741";
	google_ad_width = 300;
	google_ad_height = 250;
	//-->
	</script>
	<script type="text/javascript"
	src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
	</script>
	</body> </html>';
}
function adListener($server){
	$ref = $server['HTTP_REFERER'];
	$ref = explode("/",$ref);
	
	if(in_array("adsense_ad.php?type=others",$ref)){
		return "ad";
	}else{
		return "no";
	}
	
}
function getAllPostUnder($category_type="destination"){
	
}

function getPost($id=0){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT * FROM ".$table_prefix."posts WHERE id='".$id."'";
	$result = $wpdb->get_row($sql);
	return $result;
}

function backgroundStyle($destination=""){
	global $wpdb;
	global $table_prefix;
	
	if($destination == ""){
		$sql = "SELECT filename FROM ".$table_prefix."styles  ORDER BY RAND() LIMIT 1";
	}else{
		$sql = "SELECT filename FROM ".$table_prefix."styles  WHERE filename LIKE '%".$destination."%' LIMIT 1";
	}
	$result = $wpdb->get_row($sql);
	return $result->filename;
}

function searchCatInfoBySlug($slug){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT * FROM ".$table_prefix."terms as terms WHERE terms.slug LIKE '%".$slug."%'";
	$result = $wpdb->get_results($sql);
	return $result;
}

function getCatInfo($term_id){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT * FROM ".$table_prefix."terms as terms WHERE terms.term_id='".$term_id."'";
	$result = $wpdb->get_row($sql);
	return $result;
}
function titleMaker($obj,$req=""){
	global $arrCatType;
	
	if(isset($req) && $req!= ""){
		switch($req){
			case "destination":
				$catname = "Tourists Spots in the ";
			break;
			case "hotel":
				$catname = "Hotels in the ";
			break;
			case "restaurant":
				$catname = "Restaurants in the ";
			break;
			case "beach":
				$catname = "Beaches in the ";
			break;
			case "resort":
				$catname = "Resorts in the ";
			break;
			case "festival":
				$catname = "Festivals in the ";
			break;
			
			case "beach-and-resort":
			case "beach resort":
				$catname = "Beach Resorts in the ";
			break;
		}
	}else{
		$childcat = getCatInfo($obj->parent);
		if(in_array($childcat->name,$arrCatType)){
			$ccname = $childcat->name;
		}else{
			$ccname = $obj->name;
		}
		
		switch(strtolower($ccname)){
			case "destination":
				$catname = "Tourists Spots in ";
			break;
			case "hotel":
				$catname = "Hotels in ";
			break;
			case "restaurant":
				$catname = "Restaurants in ";
			break;
			case "beach":
				$catname = "Beaches in ";
			break;
			case "resort":
				$catname = "Resorts in ";
			break;
			case "festival":
				$catname = "Festivals in the ";
			break;
			case "beach resort":
				$catname = "Beach Resorts in ";
			break;
			case "beach and resorts":
				$catname = "Beach and Resorts in ";
			break;

		}
	}
	
	if(isset($obj->name) && $obj->name != ""){
		if($obj->parent == 0){
			$catname .= $obj->name;
		}else{
			$childcat = getCatInfo($obj->parent);
			if(in_array($childcat->name,$arrCatType)){
				$catname .= $obj->name;
			}else{
				$catname .= $childcat->name;
			}
			if(trim($catname) != "Philippines"){
				$catname .= " Philippines";
			}else{
				$catname = trim($catname);
			}
			
		}
	}
	
	
	
	return $catname;
	
}
function getChildCategory($parent_id){
	global $wpdb;
	global $table_prefix;
	
	$sql = 'SELECT 
				terms.term_id,
				terms.name,
				terms.slug,
				taxonomy.term_taxonomy_id,
				taxonomy.taxonomy,
				taxonomy.parent AS primary_parent
			FROM
				'.$table_prefix.'terms AS terms
			INNER JOIN
				'.$table_prefix.'term_taxonomy AS taxonomy ON terms.term_id = taxonomy.term_id
			WHERE
				taxonomy.taxonomy = "category" AND
				taxonomy.parent = "'.$parent_id.'"';
	
	$result = $wpdb->get_results($sql);
	return $result;
}
function getParentCatUnder($parent_id){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT 
					terms.term_id,
					terms.name,
					terms.slug,
					taxonomy.term_taxonomy_id,
					taxonomy.taxonomy,
					taxonomy.parent AS primary_parent";
		if($parent_id >= 1){
			$sql .= ",
					taxonomy2.parent AS secondary_parent";
		}else{
			$sql .= ", '0' as secondary_parent";
		}
		
		$sql .=	" FROM
					".$table_prefix."terms AS terms
				INNER JOIN
					".$table_prefix."term_taxonomy AS taxonomy ON terms.term_id = taxonomy.term_id";
					
		if($parent_id >= 1){
			$sql .=	" INNER JOIN 
					".$table_prefix."term_taxonomy AS taxonomy2 ON taxonomy.parent = taxonomy2.term_id";
		}
		$sql .= " WHERE
					taxonomy.taxonomy = 'category' AND
					taxonomy.parent = '".$parent_id."'";
					
	$result = $wpdb->get_results($sql);
	
	return $result;
}
function getAllCategoriesUnder($parent_id){
	$categories = Array();
	
	$result = getParentCatUnder($parent_id);
	$categories[] = $result;
	$current = (int)$result[0]->secondary_parent;
	
	$x=$current;
	
	while($x>0){
		
		$result = getParentCatUnder($current);
		$categories[] = $result;
		$current = (int)$result[0]->secondary_parent;
		$x = $current;
		if($x == 0){
			/*$result = getParentCatUnder("0");
			$categories[] = $result;
			$current = (int)$result[0]->secondary_parent;*/
			break;
		}
	}
	return $categories;
}

function alterUserVote($user_id,$comment_id,$vote){
	global $wpdb;
	global $table_prefix;
	
	$newVote = $vote;
	$data = array("current_vote"=>$newVote);
	$where = array("comment_id"=>$comment_id, "user_id"=>$user_id);
	$result = $wpdb->update($table_prefix."comment_vote",$data ,$where);
	return $result;
}
function addUserVote($user_id,$comment_id,$vote){
	global $wpdb;
	global $table_prefix;
	
	$data = array("comment_id"=>$comment_id,
				  "user_id"=>$user_id,
				  "current_vote"=>$vote);
	$table = $table_prefix."comment_vote";
	
	$result = $wpdb->insert($table, $data);
	return $result;
}
function checkCommentVoteStatus($user_id,$comment_id){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT * FROM
			".$table_prefix."comment_vote
			WHERE
				user_id = '".$user_id."'
				
				AND
				comment_id='".$comment_id."'";

	$result = $wpdb->get_row( $sql );
	return $result;
}

function helpfulVote($comment_id,$comment_vote, $action_type="normal"){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT 
				comment_id,
				comment_title,
				likes,
				dislikes
			FROM
			".$table_prefix."ratings

			WHERE 
				comment_id='".$comment_id."' LIMIT 1";
				
	$result = $wpdb->get_row( $sql );
	
	switch($comment_vote){
		case "yes":
			if($action_type == "change"){
				$newYesVote = ($result->likes)+1;
				$newNoVote = ($result->dislikes)-1;
				$data = array("likes"=>$newYesVote,"dislikes"=>$newNoVote);
				$where = array("comment_id"=>$comment_id);
				$result = $wpdb->update($table_prefix."ratings",$data ,$where);
			}else{
				$newYesVote = ($result->likes)+1;
				$data = array("likes"=>$newYesVote);
				$where = array("comment_id"=>$comment_id);
				$result = $wpdb->update($table_prefix."ratings",$data ,$where);
			}
		break;
		
		case "no":
			if($action_type == "change"){
				$newYesVote = ($result->likes)-1;
				$newNoVote = ($result->dislikes)+1;
				$data = array("likes"=>$newYesVote,"dislikes"=>$newNoVote);
				$where = array("comment_id"=>$comment_id);
				$result = $wpdb->update($table_prefix."ratings",$data ,$where);
			}else{
				$newNoVote = ($result->dislikes)+1;
				$data = array("dislikes"=>$newNoVote);
				$where = array("comment_id"=>$comment_id);
				$result = $wpdb->update($table_prefix."ratings",$data ,$where);
			}
		break;
	}
	return $result;
}

function getUserCommentSummary($user_id){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT 
				COUNT(id) AS total_comments,
				SUM(likes) AS total_likes,
				SUM(dislikes) AS total_dislikes,
				author_id 

			FROM
			".$table_prefix."ratings

			WHERE author_id = 4";
	$result = $wpdb->get_row( $sql );
	return $result;
}

function getCommentRating($comment_id){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT 
				COUNT(id) AS total,
				COUNT(id)*100 AS overAllTotal,
				SUM(ratings) AS sumRate,
				FLOOR((SUM(ratings)/COUNT(id))*20) AS overAllRatings,
				comment_title
			FROM
			".$table_prefix."ratings

			WHERE 
				comment_id='".$comment_id."' LIMIT 1";
				
	$result = $wpdb->get_row( $sql );
	if($result->total <=0){
		$result->overAllTotal = 0;
		$result->sumRate = 0;
		$result->overAllRatings = 0;
	}
	return $result;
}

function getPostRatings($post_id){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT 
				COUNT(id) AS total,
				COUNT(id)*100 AS overAllTotal,
				SUM(ratings) AS sumRate,
				FLOOR((SUM(ratings)/COUNT(id))*20) AS overAllRatings
			FROM
				".$table_prefix."ratings AS ratings
				
			WHERE
				post_id='".$post_id."'";
	$result = $wpdb->get_row( $sql );
	if($result->total <=0){
		$result->overAllTotal = 0;
		$result->sumRate = 0;
		$result->overAllRatings = 0;
	}
	return $result;
}

function getPostUnder($category_id){
	global $wpdb;
	global $table_prefix;
}

function getOtherInfo($post_id){
	global $wpdb;
	global $table_prefix;
	
	$sql = 'SELECT * FROM
				'.$table_prefix.'other_info
			WHERE
				post_id="'.$post_id.'"';
	$result = $wpdb->get_row( $sql );
	return $result;
}

function categorySpecific($category_type="destination"){
	global $wpdb;
	global $table_prefix;
	global $wp_query;
	
	$sql = 'SELECT
				terms.term_id AS parent_id,
				terms.slug AS parent_slug,
				terms.name AS category_location,
				terms2.name AS category_type,
				terms2.slug,
				terms2.term_id AS cat_id
			FROM
				'.$table_prefix.'terms AS terms
				
			INNER JOIN
				'.$table_prefix.'term_taxonomy AS taxonomy ON terms.term_id = taxonomy.term_id
			INNER JOIN
				'.$table_prefix.'term_taxonomy AS taxonomy2  ON taxonomy2.parent = terms.term_id
			INNER JOIN
				'.$table_prefix.'terms AS terms2 ON taxonomy2.term_id = terms2.term_id
				
			WHERE
				taxonomy.taxonomy = "category"
				
				AND terms2.slug LIKE "%'.$category_type.'%"';
				
	$results = $wpdb->get_results( $sql );
	$wpdb = $wpdb;
	return $results;
}
function getMenu($type="destination"){
	global $wpdb;
	global $table_prefix;
	$sql = 'SELECT
				terms.term_id AS parent_id,
				terms.slug AS parent_slug,
				terms.name AS category_location,
				terms2.name AS category_type,
				terms2.slug,
				terms2.term_id AS cat_id
			FROM
				'.$table_prefix.'terms AS terms
				
			INNER JOIN
				'.$table_prefix.'term_taxonomy AS taxonomy ON terms.term_id = taxonomy.term_id
			INNER JOIN
				'.$table_prefix.'term_taxonomy AS taxonomy2  ON taxonomy2.parent = terms.term_id
			INNER JOIN
				'.$table_prefix.'terms AS terms2 ON taxonomy2.term_id = terms2.term_id
				
			WHERE
				taxonomy.taxonomy = "category"
				
				AND terms2.name LIKE "%'.$type.'%"
				
				ORDER BY terms.term_id ASC';
				
	$results = $wpdb->get_results( $sql );
	return $results;
}

function getEvents($limit){
	global $wpdb;
	global $table_prefix;
	
	$date = date("Y-m-d 00:00:00");
	
	$sql = "SELECT * FROM
				".$table_prefix."calendar
			WHERE
				dateOfActivity >= '".$date."'
			ORDER BY dateOfActivity ASC	
			";
	if($limit != ""){
		$sql .= " LIMIT ".$limit;
	}
			
	$results = $wpdb->get_results( $sql );
	return $results;
}

function getRecentComments($limit=5,$post_id=""){
	global $wpdb;
	global $table_prefix;
	global $thumbdir;
	global $blogurl;
	
	$sql = "SELECT 
				posts.post_title,
				posts.guid,
				comments.comment_author,
				comments.comment_author_email,
				comments.comment_author_url,
				comments.comment_date,
				comments.comment_date_gmt,
				comments.comment_content,
				comments.user_id,
				comments.comment_ID
			FROM
				".$table_prefix."comments AS comments
			INNER JOIN
				".$table_prefix."posts AS posts ON comments.comment_post_ID = posts.ID";
				
	if($post_id != ""){
		$sql .= " WHERE posts.ID = '".$post_id."' ";
	}
	$sql .=	" ORDER BY comments.comment_ID DESC 	LIMIT ".$limit;
	$rows = $wpdb->get_results( $sql );
	return $rows;
}

function sideTripFilter($category_type="",$category_location=""){
	global $wpdb;
	global $table_prefix;
	
	$isSingle = explode("|",$category_location);
	
	$sql = "SELECT
				*
			FROM
				".$table_prefix."terms AS terms

			INNER JOIN
				".$table_prefix."term_taxonomy AS taxonomy ON terms.term_id = taxonomy.term_id


			WHERE
				
				taxonomy.taxonomy = 'category'";
				
	if(in_array("single",$isSingle)){			
		if($category_type != ""){			
			$sql .= " AND terms.slug LIKE '%".trim($category_type)."%'";
		}
	}else{
		if($category_type != ""){			
			$sql .= " AND terms.name LIKE '%".trim($category_type)."%'";
		}
	}
	
	
	if(in_array("single",$isSingle)){
		$sql .= "AND terms.slug LIKE '%".trim($isSingle[0])."%'";
	}else{
		
		if($category_location != ""){
			$sql .= "AND terms.slug LIKE '%".trim($isSingle[0])."%'";
		}
		
	}
	echo $sql;
	$result = $wpdb->get_results( $sql );
	return $result;
}

function getSideTrips($location,$limit=5){
	global $wpdb;
	global $table_prefix;
	global $thumbdir;
	global $blogurl;
	
	$sql = 'SELECT 
				"" AS thumb,
				/*SUM(rates.ratings) AS totalRate, */
				"" AS ratings,
				locInfo.*,
				posts.guid,
				posts.post_name,
				posts.post_title,
				posts.post_content,
				"thumb"
			FROM
				'.$table_prefix.'other_info AS locInfo

			/*INNER JOIN
				'.$table_prefix.'ratings AS rates ON locInfo.post_id = rates.post_id*/
			INNER JOIN
				'.$table_prefix.'posts AS posts ON locInfo.post_id = posts.ID ';
				
	if(isset($location)){
		$sql .= 'WHERE
					  locInfo.location_address LIKe "%'.$location.'%"
					AND posts.post_status="publish" ';
	}else{
		$sql .= 'WHERE posts.post_status="publish" ';
	}
				
	$sql .= 'GROUP BY locInfo.post_id
			 ORDER BY posts.ID DESC
			/* ORDER BY totalRate DESC*/';
			
			
			 
	if(isset($limit)){
		$sql .= ' LIMIT '.$limit;
	}
	$result = $wpdb->get_results( $sql );
	
	$finalResult = Array();
	
	foreach($result as $res){
		$rating = getPostRatings($res->post_id);
		$img = getImage($res->post_id);
		
		$img = $img->thumb;
		$img2= $blogurl.$thumbdir.$img;
		if(file_exists($img2)){
			$img = $thumbdir.$img;
		}else{
			$img = "/gray.jpg";
		}
		
		$res->thumb = $img;
		
		$res->ratings = array("total"=>$rating->total,
							  "rate"=>$rating->overAllRatings);
	}
	return $result;
}

function getUserRate($post_id){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT 
				post_id,
				COUNT(id) AS total_respondents,
				SUM(ratings) AS total_rate 
			FROM
			".$table_prefix."ratings
			WHERE post_id = '".$post_id."'
			GROUP BY post_id";

	$result = $wpdb->get_row( $sql );
	return $result;
}

function getImage($post_id,$limit=1){
	global $wpdb;
	global $table_prefix;
	
	$sql = "SELECT * FROM ".$table_prefix."images WHERE post_id='".$post_id."'";
	if(isset($limit)){
		$sql .= ' LIMIT '.$limit;
	}
	if($limit <= 1){
		$result = $wpdb->get_row( $sql );
	}else{
		$result = $wpdb->get_results( $sql );
	}
	return $result;
}

function rearrangeCategory($arrcat, $arrArrangement){
	$tmpCat = Array();
	$tmpCatNotInList = Array();
	$finalNotInList = Array();
	
	foreach($arrArrangement as $arrItems){
		$x=0;
		foreach($arrcat as $wpCat){
			if(in_array($arrItems,get_object_vars($wpCat))){
				$tmpCat[] = $arrcat[$x];
			}else{
				if(!in_array($wpCat->name,$tmpCatNotInList)){
					$tmpCatNotInList[$wpCat->name] = $wpCat->name;
				}
			}
		$x++;
		}
	}
	
	unset($arrItems,$x);
	
	foreach($tmpCat as $tempCat){
		$listName = $tempCat->name;
		foreach($tmpCatNotInList as $notinlist){
			if($listName == $notinlist){
				unset($tmpCatNotInList[$notinlist]);
			}
		}
	}
	
	unset($tempCat,$key);
	
	foreach($tmpCatNotInList as $notinlist){
		$key=0;
		foreach($arrcat as $origArray){
			$origArrayName = $origArray->name;
			if($origArrayName == $notinlist){
				$finalNotInList[] = $arrcat[$key];
			}
			$key++;
		}
	}
	unset($key,$notinlist,$origArray,$tmpCatNotInList,$arrcat,$arrArrangement);
	
	$mergedArray = array_merge($tmpCat,$finalNotInList);
	return ($mergedArray);
	
}

function restrict_admin_with_redirect() {
	if (!current_user_can('manage_options') && $_SERVER['PHP_SELF'] != '/wp-admin/admin-ajax.php') {
	wp_redirect("/dashboard/" ); exit;
  }
}

function wplogin_filter( $url, $path, $orig_scheme )
{
 $old  = array( "/(wp-login\.php)/");
 $new  = array( "dashboard-login");
 return preg_replace( $old, $new, $url, 1);
}



add_action('admin_init', 'restrict_admin_with_redirect');
add_filter('site_url',  'wplogin_filter', 10, 3);

function QueryParent($catID,$wpdb){
	$tablePrefix = $wpdb->prefix;
	$sql = "SELECT 
				* 
			FROM
				".$tablePrefix."term_taxonomy AS taxonomy
			
			INNER JOIN 
				".$tablePrefix."terms AS terms ON taxonomy.parent = terms.term_id
			WHERE
			
				taxonomy.term_id = '".$catID."' LIMIT 1";
	
	$result = $wpdb->get_row($sql);
	return $result;
}

function getParentCat($catID,$wpdb){
	$catList = Array();
	$result = QueryParent($catID,$wpdb);
	if(count($result)>=1){
		$catList[] = $result;
		$initialParent = $result->parent;
		for($x=$initialParent;$x>=0;$x--){
				$result = QueryParent($initialParent,$wpdb);
				if(count($result)>=1){
					$catList[] = $result;
					$initialParent = $result->parent;
					$x=$initialParent;
				}else{
					break;
				}
		}
		return $catList;
	}else{
		return null;
	}	
}

function PinoyPagination($wp_query_obj="", $pages = '', $range = 4)
{ 
	
	global $wpdb;
	global $wp_query;
	
	if($wp_query_obj == ""){
		global $wp_query;
	}else{
		$wp_query = $wp_query_obj;
	}

     $showitems = ($range * 2)+1; 
 
     global $paged;
     if(empty($paged)) $paged = 1;
 
     if($pages == '')
     {
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         if(!$pages)
         {
             $pages = 1;
         }
     }  
 
     if(1 != $pages)
     {
         echo "<div class=\"pagination\"><span>Page ".$paged." of ".$pages."</span>";
         if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a href='".get_pagenum_link(1)."'>&laquo; First</a>";
         if($paged > 1 && $showitems < $pages) echo "<a href='".get_pagenum_link($paged - 1)."'>&lsaquo; Previous</a>";
 
         for ($i=1; $i <= $pages; $i++)
         {
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
             {
                 echo ($paged == $i)? "<span class=\"current\">".$i."</span>":"<a href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
             }
         }
 
         if ($paged < $pages && $showitems < $pages) echo "<a href=\"".get_pagenum_link($paged + 1)."\">Next &rsaquo;</a>"; 
         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($pages)."'>Last &raquo;</a>";
         echo "<br clear='all' /></div>\n";
     }
}



function getSpecific($address,$type){
	global $table_prefix;
	global $wpdb;
	$sql = 'SELECT 
		posts.post_title,
		posts.post_name,
		posts.guid,
		info.post_id AS post_id,
		info.location_address,
		info.contact_number,
		info.email,
		info.website,
		info.google_map_coordinate,
		info.contact_person,
		info.transportation,
		terms.slug,
		terms.name
	FROM 

	'.$table_prefix.'other_info AS info

	LEFT JOIN '.$table_prefix.'term_relationships AS rel ON info.post_id = rel.object_id

	LEFT JOIN '.$table_prefix.'term_taxonomy AS tax ON rel.term_taxonomy_id = tax.term_taxonomy_id

	LEFT JOIN '.$table_prefix.'terms AS terms ON tax.term_id = terms.term_id
	
	LEFT JOIN '.$table_prefix.'posts AS posts ON info.post_id = posts.ID

	WHERE 
		location_address LIKE "%'.$address.'%" 
		  AND tax.taxonomy = "category" 
		  AND terms.name = "'.$type.'" LIMIT 5';
		  
	$result = $wpdb->get_results($sql);
	return ($result);
}

?>