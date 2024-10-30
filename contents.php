<?php 
	
global $wpdb;
$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'icerikbulutu_apikey';
$resultscontent = $wpdb->get_var( "SELECT apikey FROM $table_name");


?>
<script>
jQuery(document).ready(function($) {
    $('.toggle').click(function(e) {
        e.preventDefault();

        var $this = $(this);

        if ($this.next().hasClass('show')) {
            $this.next().removeClass('show');
            $this.next().slideUp(350);
            $this.parent().find('.talep-arrow').css("transform", "rotate(0deg)");
        } else {
            $this.parent().parent().find('li .inner').removeClass('show');
            $this.parent().parent().find('li .inner').slideUp(350);
            $this.next().toggleClass('show');
            $this.next().slideToggle(350);
            $this.parent().find('.talep-arrow').css("transform", "rotate(180deg)");
        }
    });
    $("#select_all").change(function(){  //"select all" change 
        $(".allcheck").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
    });

    $('.allcheck').change(function(){ 
        //uncheck "select all", if one of the listed checkbox item is unchecked
        if(false == $(this).prop("checked")){ //if this item is unchecked
            $("#select_all").prop('checked', false); //change "select all" checked status to false
        }
        //check "select all" if all checkbox items are checked
        if ($('.allcheck:checked').length == $('.allcheck').length ){
            $("#select_all").prop('checked', true);
        }
    });
    $('.button').prop("disabled", true);
    $('input:checkbox').click(function() {
        if ($(this).is(':checked')) {
        $('.button').prop("disabled", false);
        } else {
        if ($('.checks').filter(':checked').length < 1){
        $('.button').attr('disabled',true);}
        }
    });
    $("#dropdown-sort").on('change', function(){
    	var val = $(this).val(); 
    	var t4 = $('.talep4').length;
    	var t10 = $('.talep10').length;
    	if(val == 3){
    		$('.talep4').hide();
    		$('.talep10').show();
    		$('.talepno').hide();
    	}
    	if(val == 2){
    		$('.talep10').hide();
    		$('.talep4').show();
    		$('.talepno').hide();
    	}
	    if(val == 1){
    		$('.talep4').show();
    		$('.talep10').show();
    		$('.talepno').hide();
    	} 
    	if(val == 3 && t10 == 0){
    		$('.talep4').hide();
    		$('.talep10').show();
    		$('.talepno').show();
    	} 
    	if(val == 2 && t4 == 0){
    		$('.talep10').hide();
    		$('.talep4').show();
    		$('.talepno').show();
    	}
    	if(val == 1 && t4 == 0 && t10 == 0){
    		$('.talep4').show();
    		$('.talep10').show();
    		$('.talepno').show();
    	} 

	});
});
</script>
<?php

$data_array =  array(
  "Entity" => array(
        "CurrentPage" => 1,
        "PageSize"=>100000,
        "Parameter"=>array(
            "Company"=>array(
                "WordpressKey"=>$resultscontent
            )
        )

  ),
);

$make_call = callAPI('POST', 'https://api.icerikbulutu.com/v2/word-press/advert/list', json_encode($data_array));
$talep = json_decode($make_call,true);
echo "<div class='wrap'>";
echo "<h1 class='wp-heading-inline'>". __( 'Requests', 'icerik-bulutu' )." (".$talep["Entity"]["Count"].")</h1>";
echo "<select id='dropdown-sort' name='select'>
	  <option selected value='1'>". __( 'All Requests', 'icerik-bulutu' )."</option>
      <option value='2'>". __( 'Blog Requests', 'icerik-bulutu' )."</option>
      <option value='3'>". __( 'List Requests', 'icerik-bulutu' )."</option>
      </select>";
$temp = "<div id='talepler'>";
$temp .= "<form action='/wp-admin/admin.php?page=icerik-bulutu-import' method='post'>";
$temp .= "<ul class='accordion'>";

$temp .= "<div class='selectall'><input type='checkbox' class='al' id='select_all' name='all'/>".__( 'Select All', 'icerik-bulutu' )."</div>" ;
$temp .= "<p class='talepno'>".__( 'Requests Not Found', 'icerik-bulutu' )."</p>";
for($i = 0; $i < $talep["Entity"]["Count"]; $i++){
    if($talep["Entity"]["Collection"][$i]["WrittenArticleCount"] > 0){
        $temp .= "<li id='talep' class='talep".$talep["Entity"]["Collection"][$i]["AdvertTypeId"]."'>";
        $temp .= '<input type="checkbox" class="allcheck" onclick="CheckAll(\'form'.$i.'\', this)" name="all"/>' ;
        $temp .= "<a class='toggle' href='javascript:void(0);' id='talep-content'>";
        $temp .= "<div class='talep-title'>" . $talep["Entity"]["Collection"][$i]["Title"] . "</div>";
        if($talep["Entity"]["Collection"][$i]["AdvertTypeId"] = 4){
    		$temp .= "<div class='talep-cat'>".__( 'Blog Articles', 'icerik-bulutu' )."</div>";
    	}elseif($talep["Entity"]["Collection"][$i]["AdvertTypeId"] = 10){
    		$temp .= "<div class='talep-cat'>".__( 'List Articles', 'icerik-bulutu' )."</div>";
    	}
        $temp .= "<div class='talep-article-count'>".__( 'Total Article Count:', 'icerik-bulutu' )." " . $talep["Entity"]["Collection"][$i]["ArticleCount"] . "</div>";
        $temp .= "<div class='talep-done'>".__( 'Written Article Count:', 'icerik-bulutu' )." " . $talep["Entity"]["Collection"][$i]["WrittenArticleCount"] . "</div>";
        $date = new DateTime($talep["Entity"]["Collection"][$i]["CreatedOn"]);
        $temp .= "<div class='talep-date'>".__( 'Date:', 'icerik-bulutu' )." " . $date->format('d/m/Y - H:i:s') . "</div>";
        $temp .= "<svg class='talep-arrow' height='6' viewBox='0 0 10 6' width='10' xmlns='http://www.w3.org/2000/svg'><path d='m1 1 4 4 4-4' fill='none' stroke='#0073aa' stroke-linecap='round' stroke-linejoin='round' stroke-width='2'/></svg>";
        $temp .= "</a>";

    	$data_array_icerik =  array(
          "Entity" => array(
                "CurrentPage" => 1,
                "PageSize"=>100000,
                "Parameter"=>array(
                	"AdvertId"=>$talep["Entity"]["Collection"][$i]["Id"],
                	"Advert"=>array(
    	                "Company"=>array(
    	                    "WordpressKey"=>$resultscontent
    	                )
    	            )
                )

          ),
        );
        $make_call_icerik = callAPI('POST', 'https://api.icerikbulutu.com/v2/word-press/article/list', json_encode($data_array_icerik));
        $icerik = json_decode($make_call_icerik,true);

        $temp .= "<ul id='icerikler' class='inner'>";

        for($j = 0; $j < $talep["Entity"]["Collection"][$i]["WrittenArticleCount"]; $j++){
        	$temp .= "<div id='icerik'>";
        	$temp .= "<div id='icerik-t'>";
            $temp .= "<input type='checkbox' class='form".$i." allcheck' value='".$icerik["Entity"]["Collection"][$j]["Id"]."' name='huy[]' />";
        	$temp .= "<div class='icerik-title'>" . $icerik["Entity"]["Collection"][$j]["ContentTitle"] . "</div>";
        	$icerik_date = new DateTime($icerik["Entity"]["Collection"][$j]["CreatedOn"]);
        	$temp .= "<div class='icerik-date'>".__( 'Date:', 'icerik-bulutu' )." " . $icerik_date->format('d/m/Y - H:i:s') . "</div>";
        	$temp .= "</div>";
        	$temp .= "</div>";
        }
        $temp .= "</ul>";
        $temp .= "</li>";
    }
}
$temp .= "</ul>";
$temp .= "<input type='submit' name='formSubmit' class='button' value='".__( 'Import Selected', 'icerik-bulutu' )."' />";
$temp .= "</div>";
$temp .= "</form>";
echo $temp;

?><script>
function CheckAll(className, elem) {
    var elements = document.getElementsByClassName(className);
    var lq = elements.length;

    if (elem.checked) {
        for (var iq = 0; iq < lq; iq++) {
            elements[iq].checked = true;
        }
    } else {
        for (var iq = 0; iq < lq; iq++) {
            elements[iq].checked = false;
        }
    }
}    
</script>
</div>
