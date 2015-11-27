<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function open_csa_wp_show_order_form ($user_id, $spot_id, $delivery_id, $display, $page_url, $personal_order){

	wp_enqueue_script( 'open-csa-wp-general-scripts' );
	wp_enqueue_script( 'open-csa-wp-orders-scripts' );
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');
	
	global $wpdb;
	$csa_data = null;
	$spot_id_preference = null;	

	//If it is a personal order, fetch the meta data of the user. Update spot_id with the information from the meta data(Maybe wrong but let's roll with it now)
	if ($personal_order === true)	{
		$csa_data = get_user_meta( $user_id, 'open-csa-wp_user', true );
		if (isset($csa_data['spot']))
			$spot_id_preference = $csa_data['spot'];
	}

	//Apo pou erxetai to spot_id?!?! Mporei na erthei apo kapou??? Psajimo
	if ($spot_id != null) {
		$spot_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". OPEN_CSA_WP_TABLE_SPOTS ." WHERE id=%d", $spot_id))[0];	
	}
	


	$edit_order_bool = false;
	$header_text = __("Submit New Order", OPEN_CSA_WP_DOMAIN);
	if (isset($_POST["open-csa-wp-showEditableUserOrderForm_user_input"]) OR (
				$user_id != null && 
				$delivery_id != null &&
				open_csa_wp_user_order_exists_for_delivery($user_id, $delivery_id) === true
			)
		) {
		$edit_order_bool = true;
		$header_text = __("Edit Order",OPEN_CSA_WP_DOMAIN);
	}
?>

	<br/>

	<div id="open-csa-wp-showNewOrderForm_formHeader">
		<span 
			id="open-csa-wp-showNewOrderForm_formHeader_text"
			style="cursor:pointer"
			onclick="open_csa_wp_toggle_form('showNewOrderForm','<?php echo $header_text; ?>', ' <?php _e('form', OPEN_CSA_WP_DOMAIN) ?>');"
		>
		<?php
			if ($display === false) {
				echo "<font size=4> $header_text (".__("show form",OPEN_CSA_WP_DOMAIN).") </font>";
			} else {
				echo "<font size=4> $header_text (".__("hide form",OPEN_CSA_WP_DOMAIN).") </font>";
			}
		?>
		</span>
	</div>
	<div 
		id="open-csa-wp-showNewOrderForm_div" 
		<?php 
			if ($display === false) {
				echo 'style="display:none"';
			}
		?>	
	>
		<form method="POST" id="open-csa-wp-showNewOrderForm_form_id">
			<table class="form-table">
				<tr valign="top" 
					<?php 
						if ($personal_order === true) {
							echo "style='display:none'"; 
						}
					?>
				>
					<td id = "open-csa-wp-showNewOrderForm_user_input_td_id">
						<select
							name = 'open-csa-wp-showNewOrderForm_user_input'
							onchange = 'getElementById ("open-csa-wp-showNewOrderForm_form_id").submit()'
						>
							<option
								value=""
								selected="selected"
								disabled="disabled"
							> <?php _e('Select the user submitting the order',OPEN_CSA_WP_DOMAIN); ?> ...
							</option>
							<?php open_csa_wp_select_users($user_id, __('for user',OPEN_CSA_WP_DOMAIN).": ");?>
						</select>
					</td>
				</tr>
				
				<tr valign="top"
				<?php 
					if ($user_id === null) {
						echo "style = 'display:none'";
					}
				?>
				>
				<td>
					<!-- Here starts the creation of the select element regarding the delivery spot -->
					<select
						name = 'open-csa-wp-showSelectSpotForm_spot_input'
						<?php //---[By eliminating this code we can provide to the user the choice to select another delivery spot instead of his prefered one.]---
							//If there is a delivery spot preference, select the default message
							if ($spot_id_preference !== null) {
								echo 'disabled="disabled" ';
							} 
						?>
						onchange = 'getElementById("open-csa-wp-showNewOrderForm_form_id").submit();'
						
					>
						<option 
							value="" 
							<?php 
								//If a spot_id is not present, select the default message
								if ($spot_id === null && $spot_id_preference === null) {
									echo 'selected="selected" ';
								} 
							?>
							disabled="disabled"
						> 
						<?php
						if ($personal_order === false) {
							_e("now, select the delivery spot ...",OPEN_CSA_WP_DOMAIN);
						} else {
							_e("select the delivery spot ...",OPEN_CSA_WP_DOMAIN);
						}
						?>
						
						</option>
						<!-- Populate the options by calling the function 'open_csa_wp_select_delivery_spots',
						     provide delivery spot preference(if present) if a spot_id is not provided-->
						<?php 	
							if($spot_id === null && $spot_id_preference !== null)
								$spot_id = $spot_id_preference;
							open_csa_wp_select_delivery_spots($spot_id, __("on delivery spot",OPEN_CSA_WP_DOMAIN).": ");
						?>
					</select>
				</td>
				</tr>
				
				<tr valign = "top"
				<?php 
					if ($spot_id == null) {
						echo "style = 'display:none'";
					}
				?>
				><td>
				
				
				
				<div id="open-csa-wp-showNewOrderForm_spotDetails_formHeader">
					<span 
						id="open-csa-wp-showNewOrderForm_spotDetails_formHeader_text"
						style="cursor:pointer"
						onclick="open_csa_wp_toggle_form('showNewOrderForm_spotDetails','<?php _e('spot details',OPEN_CSA_WP_DOMAIN); ?>...', '', 2);"
					><font size=2> <?php echo __('spot details',OPEN_CSA_WP_DOMAIN). "... (". __('show',OPEN_CSA_WP_DOMAIN) .")"; ?> </font>
					</span>
				</div>
				<div 
					id="open-csa-wp-showNewOrderForm_spotDetails_div" 
					style="display:none"
				>
				
				<?php 
					if ($spot_id != null) {				
						echo "
							<table>
							<tr valign='top'><td>
								<input 
									type='text' 
									style='border:none; background-color:white; color:#999'
									readonly='readonly'
									value='".$spot_info->street_name."'
								/>
								<input 
									type='text' 
									style='border:none; background-color:white; color:#999'
									readonly='readonly'
									value='".$spot_info->street_number."'
								/>
							</td></tr>
							<tr valign='top'><td>
								<input 
									type='text' 
									style='border:none; background-color:white; color:#999'
									readonly='readonly'
									value='".$spot_info->city."'
								/>
								<input 
									type='text' 
									style='border:none; background-color:white; color:#999'
									readonly='readonly'
									value='".$spot_info->region."'
								/>
							</td></tr>
							<tr valign='top'><td>
								<textarea 
									style='border:none; background-color:white; color:#999'
									readonly='readonly'
									rows='3' 
									cols='30' >".$spot_info->description ."</textarea>
							</td></tr>
							
							<tr valign='top'><td>
								<input 
									type='text'
									style='border:none; background-color:white; color:#999'
									readonly='readonly'
									value='parking is ". $spot_info->parking."'
								/>
							</td></tr>										
							<tr valign='top'><td>
								<input
									type='text'
									style='border:none; background-color:white; color:#999'
									readonly='readonly'
									value='".
									($spot_info->has_refrigerator == 1?
									__('It has refrigerator to store products', OPEN_CSA_WP_DOMAIN)."! :) ": 
									__('It does not have refrigerator to store products', OPEN_CSA_WP_DOMAIN)."! :(")
									."'
									size = '40px'
								/>
							</td></tr>

							
							</table>
						";
					}
				?>
				</div></td></tr>
				
				
				<tr valign = "top" 
				<?php 
					if ($spot_id == null) {
						echo "style = 'display:none'";
					}
				?>
				>
				<td id = "open-csa-wp-showSelectSpotForm_delivery_input_td_id">
					<?php
					if (open_csa_wp_active_deliveries_exist_for_spot($spot_id) == true) {
					?>
						<select
							name = 'open-csa-wp-showSelectSpotForm_delivery_input'
							onchange = 'getElementById("open-csa-wp-showNewOrderForm_form_id").submit();'
						>
							<option 
								value="" 
								selected="selected" 
								disabled="disabled"
							> <?php _e('now, select one of the active deliveries', OPEN_CSA_WP_DOMAIN); ?> ...
							</option>
							<?php 
								if ($spot_id!=null) {
									open_csa_wp_select_deliveries($spot_id, $delivery_id, __('for delivery with',OPEN_CSA_WP_DOMAIN) .": "); 
								}
							?>
						</select>
					<?php
					} else {
						$value_of_read_only_input = __("currently, this delivery spot has no active deliveries", OPEN_CSA_WP_DOMAIN);
						$value_of_read_only_input_len = strlen($value_of_read_only_input);
						$size_of_read_only_input = (($value_of_read_only_input_len + 1) ).'"px\"';
						echo "
							<input 
								type='text' 
								style='border:none; background-color:white; color:brown'
								readonly='readonly'
								value = '$value_of_read_only_input'
								size = '$size_of_read_only_input'
								
							/>
						";
					}
					?>
				</td>
			</tr>
			</table>	
		</form>
	</div>
<?php
	if ($edit_order_bool === true) {
		open_csa_wp_show_editable_user_order($user_id, $delivery_id, true, $page_url);
	} else if ($delivery_id != null) {
		open_csa_wp_show_new_order_user_form($delivery_id, $user_id, false, $page_url);	
	}
	
	if ($edit_order_bool === false && $user_id != null && $delivery_id == null && ($personal_order === false || $spot_id != null)) {
	?>
		<input 
		type="button"
		class="button button-secondary"
		value='cancel'
		onclick='window.location.replace(" <?php echo $page_url ?> ");'
		/>	
		<?php
		
	}
}

function open_csa_wp_user_order_exists_for_delivery($user_id, $delivery_id) {
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare("
				SELECT delivery_id
				FROM ".OPEN_CSA_WP_TABLE_USER_ORDERS."
				WHERE 
					delivery_id = %d AND
					user_id = %d
			",$delivery_id, $user_id)) != null?true:false;
}

function open_csa_wp_show_new_order_user_form($delivery_id, $user_id, $edit_order_bool, $page_url) {
	echo "
		<span 
			class='open-csa-wp-tip_order' 
			title=' ".
				__('Choose the quantity of each product you want to order and press "Submit" at the end of the page.', OPEN_CSA_WP_DOMAIN)."
				| ". __('After submitting your order, you will still be able to edit it, until the deadline for order submission of this delivery.',OPEN_CSA_WP_DOMAIN)."
				| ". __('You can read additional information for each product, by pointing to "info".',OPEN_CSA_WP_DOMAIN) ."
			'>
			<p style='color:green;font-style:italic; font-size:13px'>
			". __('by pointing here you can read additional information.', OPEN_CSA_WP_DOMAIN)."...</p>
		</span>";

	open_csa_wp_show_user_orders_form($delivery_id, $user_id, $edit_order_bool, $page_url);
}

function open_csa_wp_show_editable_user_order($user_id, $delivery_id, $edit_order_bool, $page_url) { 
	wp_enqueue_script( 'open-csa-wp-general-scripts' );
	wp_enqueue_script( 'open-csa-wp-orders-scripts' );
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui');
?>	
	<span 
		class='open-csa-wp-tip_order' 
		title="
			<?php _e('If you want to edit the quantity of some product order, click on it, update it, and then press ENTER.',OPEN_CSA_WP_DOMAIN);?>
			| <?php _e("To remove some product from your order, click on the 'x' icon.",OPEN_CSA_WP_DOMAIN); ?>
		">
		<p style="color:green;font-style:italic; font-size:13px">
			<?php _e('by pointing here you can read additional information',OPEN_CSA_WP_DOMAIN); ?>...</p>
	</span>	
	
	<table class='table-bordered' id="open-csa-wp-showUserOrder_table" style='border-spacing:1em'> 
	<thead class="tableHeader">
		<tr>
			<th><?php _e('Quantity',OPEN_CSA_WP_DOMAIN); ?></th>
			<th	style="text-align:center"><?php _e('Name',OPEN_CSA_WP_DOMAIN); ?></th>
			<th><?php _e('Category',OPEN_CSA_WP_DOMAIN); ?></th>
			<th><?php _e('Variety',OPEN_CSA_WP_DOMAIN); ?></th>
			<th><?php _e('Price',OPEN_CSA_WP_DOMAIN); ?>(€)</th>
			<th><?php _e('Per',OPEN_CSA_WP_DOMAIN); ?>...</th>
			<th><?php _e('Cost',OPEN_CSA_WP_DOMAIN); ?></th>
			<th><?php _e('Producer',OPEN_CSA_WP_DOMAIN); ?></th>
			<th></th>
			<th></th>
		</tr>
	</thead> 
	<tbody> 
	<?php
		//show an editable version of the user's order	
		global $wpdb;
		$product_orders = $wpdb->get_results($wpdb->prepare("
							SELECT * 
							FROM ". OPEN_CSA_WP_TABLE_PRODUCT_ORDERS ."
							WHERE
								delivery_id = %d AND
								user_id = %d
						", $delivery_id, $user_id));

		$total_cost = 0;
		$plugins_dir = plugins_url();
		
		$product_categories_map = $wpdb->get_results("SELECT id,name FROM ".OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES, OBJECT_K);
		$producers_map = open_csa_wp_producers_map_array();

		$current_user_id = get_current_user_id();
		
		foreach($product_orders as $product_order) {
			$product_id = $product_order->product_id;
			$product_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".OPEN_CSA_WP_TABLE_PRODUCTS." WHERE id=%d", $product_id))[0];
			
			$p_cost = $product_info->current_price_in_euro * $product_order->quantity;
			echo "
			<tr class='open-csa-wp-user-order-product' id='open-csa-wp-userProductOrder_".$delivery_id."_".$user_id."_".$product_id."_".$current_user_id."_". get_current_user_id() ."'>
				<td class='editable_product_order_quantity' style='text-align:center'>$product_order->quantity</td>
				<td>$product_info->name</td>
				<td>".$product_categories_map[$product_info->category]->name."
				<td>".$product_info->variety."</td>
				<td class='editable_product_order_price'>".$product_info->current_price_in_euro."</td>
				<td>".$product_info->measurement_unit."</td>
				<td class='editable_product_order_cost' style='text-align:center;font-weight:bold'> $p_cost €</td>
				<td>".$producers_map[$product_info->producer]."</td>";	
				if ($product_info->description != '') {
					echo "<td style='text-align:center'><span class='open-csa-wp-tip_order' title='|".$product_info->description."'>info</span></td>";			
				} else { 
					echo "<td/>";	
				}
				echo "<td> <img class='delete no-underline' src='$plugins_dir/open-csa-wp/icons/delete.png' style='cursor:pointer;padding-left:10px;' onmouseover='open_csa_wp_hover_icon(this, \"delete\", \"$plugins_dir\")' onmouseout='open_csa_wp_unhover_icon(this, \"delete\", \"$plugins_dir\")' onclick='open_csa_wp_request_delete_product_order(this)' title='".__('remove this product from your order', OPEN_CSA_WP_DOMAIN)."'/></td>
			</tr>"; 
			$total_cost+=$p_cost;
		}
	?>
	</tbody>
	</table>
	<div>
		<textarea 
			name="open_csa_wp_order_comments" 
			id="open_csa_wp_order_comments_id"
			cols="50" rows="3" maxlength="500" 
			class="info-text"
			<?php
				$order_comments = $wpdb->get_var($wpdb->prepare("
					SELECT comments 
					FROM ". OPEN_CSA_WP_TABLE_USER_ORDERS ."
					WHERE
						delivery_id = %d AND
						user_id = %d
				", $delivery_id, $user_id));
				
				if ($order_comments == null) {
					echo "placeholder='".__('Do you have any comments to add',OPEN_CSA_WP_DOMAIN). "...?'";
				}
			?>><?php if ($order_comments != null) {echo "$order_comments";}?></textarea>
	</div>
	<table>
		<tbody>
			<tr> 
				<td><div><?php _e('Total Cost',OPEN_CSA_WP_DOMAIN); ?>: <span id='editable_product_order_TotalCost' style='font-weight:bold'><?php echo round($total_cost,1). " €" ?></span></div></td>
				<td><div onclick="open_csa_wp_slide_toggle(document.getElementById('cancel_div'))"> 
						<img src="<?php echo plugins_url(); ?>/open-csa-wp/icons/delete.png"/ height="24" width="24"> &nbsp
						<span class='showHide_div' style='color:brown;cursor:pointer'><?php _e('Cancel Order',OPEN_CSA_WP_DOMAIN); ?></span> 
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<input 
					type="button"
					class="button button-primary"
					value='<?php _e('Done',OPEN_CSA_WP_DOMAIN); ?>'
					onclick='
							open_csa_wp_request_user_order_update(<?php echo"$delivery_id, $user_id"?>, getElementById("open_csa_wp_order_comments_id").value);
							window.location.replace(" <?php echo $page_url ?> ");
					'
					/>
				</td>
			</tr>
		</tbody></table>
	</div>
	
	<div id='cancel_div' style='display:none; '> <br/>
		<button type="button" class="button button-primary" onclick='open_csa_wp_request_cancel_user_order(<?php echo $delivery_id.", ".$user_id.", ".get_current_user_id() ?>)'><?php _e('Yes... cancel order!',OPEN_CSA_WP_DOMAIN); ?></button>
		<button type="button" class="button button-primary" style='background-color:brown' onclick="open_csa_wp_slide_toggle(document.getElementById('cancel_div'))"><?php _e('uhh... just a mistake!',OPEN_CSA_WP_DOMAIN); ?></button>
	</div>
	<br/>
	<?php open_csa_wp_show_additional_products_order_form($delivery_id, $user_id, $edit_order_bool, $page_url); 
}

function open_csa_wp_show_additional_products_order_form($delivery_id, $user_id, $edit_order_bool, $page_url) {
?>
	<div id="open-csa-wp-showAdditionalProductOrders_formHeader">
		<span 
			id="open-csa-wp-showAdditionalProductOrders_formHeader_text"
			style="cursor:pointer"
			onclick="open_csa_wp_toggle_form('showAdditionalProductOrders','<?php _e('Add more products',OPEN_CSA_WP_DOMAIN); ?>', ' <?php _e('form',OPEN_CSA_WP_DOMAIN); ?>');"
		><font size=4> <?php _e('Add more products',OPEN_CSA_WP_DOMAIN); ?> (<?php _e('show form',OPEN_CSA_WP_DOMAIN); ?>) </font>
		</span>
	</div>
	<div 
		id="open-csa-wp-showAdditionalProductOrders_div"  style='display:none'>
	<span 
		class='open-csa-wp-tip_order' 
		title=' 
			<?php _e('Choose the quantity of each product you want to order and press "Submit" at the end of the page.',OPEN_CSA_WP_DOMAIN); ?>
			| <?php _e('You can read additional information for each product, by pointing to "info".',OPEN_CSA_WP_DOMAIN); ?>
		'>
		<p style='color:green;font-style:italic; font-size:13px'>
			<?php _e('by pointing here you can read additional information',OPEN_CSA_WP_DOMAIN); ?>...
		</p>
	</span>
<?php

	open_csa_wp_show_user_orders_form($delivery_id, $user_id, $edit_order_bool, $page_url);
	echo "</div>";
}

function open_csa_wp_show_user_orders_form($delivery_id, $user_id, $edit_order_bool, $page_url) {
	//list categories as headers
	
	global $wpdb;
	$product_categories = $wpdb->get_results("SELECT DISTINCT category FROM ".OPEN_CSA_WP_TABLE_PRODUCTS);
	$product_categories_map = $wpdb->get_results("SELECT id,name FROM ".OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES, OBJECT_K);
	$producers_map = open_csa_wp_producers_map_array();
	

	foreach ($product_categories as $product_category)
			echo "(<a href='#open-csa-wp-jump_to_category_".$product_categories_map[$product_category->category]->name."'>".$product_categories_map[$product_category->category]->name."</a>)  " ;
	?>
	<br/>
	
	<form id='open-csa-wp-sumbitOrder_form_id' accept-charset='utf-8' method='post'> 
		<table id='ordersArray' class='table-bordered' style='border-spacing:1em'> 
			<thead class="tableHeader">
				<tr>
					<th><?php _e('Name',OPEN_CSA_WP_DOMAIN); ?></th>
					<th><?php _e('Variety',OPEN_CSA_WP_DOMAIN); ?></th>
					<th><?php _e('Price',OPEN_CSA_WP_DOMAIN); ?></th>
					<th><?php _e('Unit',OPEN_CSA_WP_DOMAIN); ?></th>
					<th><?php _e('Quantity',OPEN_CSA_WP_DOMAIN); ?></th>
					<th><?php _e('Producer',OPEN_CSA_WP_DOMAIN); ?></th>
					<th> </th> 
					<th class='th-hidden'>
					</th>
				</tr>
			</thead>
			<?php
			foreach ($product_categories as $product_category) {
				$products = $wpdb->get_results($wpdb->prepare("
								SELECT * 
								FROM ".OPEN_CSA_WP_TABLE_PRODUCTS." 
								WHERE category=%d ORDER BY name", $product_category->category )
							);
								
				if (count($products) > 0) {
					echo "
						<tr>
							<td> <strong> 
								<a 
									name='open-csa-wp-jump_to_category_".$product_categories_map[$product_category->category]->name."' 
									class='anchored'>
								</a> 
									<span 
										class='emphasis-box' 
										style='margin-left:-5px;'
									>".$product_categories_map[$product_category->category]->name."
									</span>
								</strong>
							</td>
						</tr>
					";
				}
				foreach($products as $row) {
					if($row->is_available == 1) {
						echo "
						<tr style='text-align:center'>
						<td><span>".$row->name."</span></td>
						<td><span>".$row->variety."</span></td>
						<td><input type='number' readonly = 'readonly' name='open-csa-wp-order_productPrice' value='".$row->current_price_in_euro."' style=' width: 5em; border:none; background-color:white; text-align: center'/></td>
						<td>€/".$row->measurement_unit."</td>
						<td><input type='number' min='0' step='0.5' name='open-csa-wp-order_productQuantity' onchange='open_csa_wp_calc_new_order_cost()' onkeyup='open_csa_wp_calc_new_order_cost()' style='width:70px;background-color:LightGoldenRodYellow'></td>
						<td>".$producers_map[$row->producer]."</td>";
						if ($row->description != null) {
							echo "<td style='text-align:center'><span class='open-csa-wp-tip_order' title='|".$row->description."'>info</span></td>";
						}
						echo "<td class='td-hidden'><input type='number' name='open-csa-wp-order_productID' value='".$row->id."' style='visibility:hidden'/></td>
						</tr>";
					}
				}
			}
			?>
			<tr style='background-color:#d0e4fe;'><td><span id='open-csa-wp-totalCalc'/></td></tr>
		</table>  
		<div>
			<textarea 
				name="open_csa_wp_order_comments" 
				cols="50" rows="3" maxlength="500" 
				class="info-text"
				placeholder="<?php _e('Do you have any comments to add...?',OPEN_CSA_WP_DOMAIN); ?>"></textarea>
		</div>
		<div>	
			<input type='button' class="button button-primary" 
				<?php
				if($edit_order_bool === true) {
					echo "value='".__('Submit Additional Order',OPEN_CSA_WP_DOMAIN)."'";
				} else {
					echo "value='".__('Submit Order',OPEN_CSA_WP_DOMAIN)."'";				}
				
				?>
				style="margin-top:1%" onclick="open_csa_wp_new_order_validation(<?php echo "$delivery_id, $user_id, ".get_current_user_id(); ?>, this)"/> 
			<?php
			if ($edit_order_bool === false) {
				echo "
					<input 
						style='display:inline;margin-top:1%'
						type='button'
						class='button button-secondary'
						value='".__('cancel', OPEN_CSA_WP_DOMAIN)."'
						onclick='window.location.replace(\" $page_url\");'
					/>
				";
			}
			?>
			<span id="open-csa-wp-showNewOrderForm_emptyOrder_span_id" style="color:brown; display:inline" /></td>
		</div>
	</form>
		
	<?php
}


function open_csa_wp_show_all_user_orders($personal_user_id, $display) {
	wp_enqueue_script('open-csa-wp-general-scripts');
	wp_enqueue_script('open-csa-wp-orders-scripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 
	
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');
	
	global $wpdb;
	$personal_user_is_responsible = false;
	if ($personal_user_id != null) {
		$user_orders_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(delivery_id) FROM ". OPEN_CSA_WP_TABLE_USER_ORDERS ." WHERE user_id = %d ", $personal_user_id));
		$personal_user_data = get_user_meta($personal_user_id, 'open-csa-wp_user', true ); 
		if ($personal_user_data != null && $personal_user_data['role'] != "none") {
			$personal_user_is_responsible = true;
		}
	} else {
		$user_orders_count = $wpdb->get_var("SELECT COUNT(delivery_id) FROM ". OPEN_CSA_WP_TABLE_USER_ORDERS);
	}
		
	if ($user_orders_count == 0) {
		if ($personal_user_id != null) {
			echo "<br/><div style='color:brown'> ".__('You have not yet submitted any order.', OPEN_CSA_WP_DOMAIN)."</div>";
		} else {
			echo "<br/><div style='color:brown'>".__('No user has yet submitted any order.', OPEN_CSA_WP_DOMAIN)."</div>";
		}
	} else {
?>
		
		<br />
		<div id="open-csa-wp-showAllUserOrdersList_header">
			<span 
				style="cursor:pointer" 
				id="open-csa-wp-showAllUserOrdersList_formHeader_text" 
				onclick="open_csa_wp_toggle_form('showAllUserOrdersList','User Orders List', '')">
				<font size='4'>
				<?php 
					if ($personal_user_id != null) {
						$text_to_show = __("Your Orders List", OPEN_CSA_WP_DOMAIN);
					} else {	
						$text_to_show = __("User Orders List", OPEN_CSA_WP_DOMAIN);
					}
				
					if ($display === true) {
						echo "$text_to_show (".__("hide", OPEN_CSA_WP_DOMAIN).")";
					} else {
						echo "$text_to_show (".__("show", OPEN_CSA_WP_DOMAIN).")";
					}
				?>
				</font>
			</span>
		</div>
		<div id="open-csa-wp-showAllUserOrdersList_div" 
			<?php 
				if ($display === false)  {
					echo 'style="display:none"';
				}
			?>	
		>
			<span class='open-csa-wp-tip_order' title='
				<?php _e('Some order in "green" states that the corresponding delivery is pending and still accepts new orders.', OPEN_CSA_WP_DOMAIN)?>
				| <?php _e('Some order in "brown" states that the corresponding delivery is pending and does not accept new orders.', OPEN_CSA_WP_DOMAIN)?>
				| <?php _e('Some order in "grey" states that the corresponding delivery is accomplished.', OPEN_CSA_WP_DOMAIN)?>
				| <?php _e('If you want to edit user order details, click on the "pen" icon.', OPEN_CSA_WP_DOMAIN);
		
					if ($personal_user_id != null) {
						echo "|". __('You can become responsible for some delivery (if none exists) by clicking on the "list" icon.', OPEN_CSA_WP_DOMAIN);
					}
				?>
				'>
			<p style="color:green;font-style:italic; font-size:13px">
				<?php _e('by pointing here you can read additional information...', OPEN_CSA_WP_DOMAIN)?></p></span>


			<table 
				class='table-bordered' 
				id="open-csa-wp-showAllUserOrdersList_table" 
				style='border-spacing:1em' 
			> 
			<thead class='tableHeader'>
				<tr>
					<?php 
						if ($personal_user_id == null) {
							echo "<th>"._('User',OPEN_CSA_WP_DOMAIN) ."</th>";
						}
					?>
					<th><?php _e('Spot', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Order Deadline Date', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Order Deadline Time', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Delivery Date', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Delivery Start Time', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Delivery End Time', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Uner In Charge', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('New Orders Can be Submitted?', OPEN_CSA_WP_DOMAIN)?></th>
					<th/>
					<?php 
						if ($personal_user_is_responsible === true) {
							echo "<th/>";
						}
					?>
				</tr>
			</thead> 
			<tbody> <?php
				global $wpdb;
				$plugins_dir = plugins_url();
				
				if ($personal_user_id == null) {
					$csa_users = get_users();
				} else {
					$csa_users = array(get_user_by('id', $personal_user_id));
				}
				
				foreach ($csa_users as $csaUser) {
				
					$deliveries = $wpdb->get_col($wpdb->prepare("SELECT delivery_id FROM ". OPEN_CSA_WP_TABLE_USER_ORDERS ." WHERE user_id = %d ", $csaUser->ID));
					
					foreach($deliveries as $delivery) {
						$delivery_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". OPEN_CSA_WP_TABLE_DELIVERIES. " WHERE id = %d", $delivery))[0];

						$delivery_id = $delivery_info->id;
						$spot_name = $wpdb->get_var($wpdb->prepare("SELECT spot_name FROM ". OPEN_CSA_WP_TABLE_SPOTS ." WHERE id=%d", $delivery_info->spot_id));
						 
						$user_in_charge_login = "";
						if ($delivery_info->user_in_charge != null ) {
							$user_in_charge_login = get_user_by('id', $delivery_info->user_in_charge)->user_login;
						}
						
						$past_delivery = false;
						$current_date_time = current_time('mysql');
						if (strtotime($delivery_info->order_deadline_date." ". $delivery_info->order_deadline_time) < strtotime($current_date_time)) {
							$past_delivery = true;
						}
						
						echo "
							<tr 
								valign='top' 
								id='open-csa-wp-showDeliveriesDeliveryID_$delivery_id'  
								class='open-csa-wp-showDeliveries-delivery'
								style='color:". (($past_delivery === true)?"gray": ($delivery_info->are_orders_open == 1?"green":"brown")) ."'
							>
							". (($personal_user_id === null)?("<td>".open_csa_wp_user_readable($csaUser)."</td>"):("")) ."
							<td style='text-align:center'>$spot_name </td>
							<td style='text-align:center'>".date(OPEN_CSA_WP_DATE_FORMAT_READABLE, strtotime($delivery_info->order_deadline_date))."</td>
							<td style='text-align:center'>".open_csa_wp_remove_seconds($delivery_info->order_deadline_time)."</td>
							<td style='text-align:center'>".date(OPEN_CSA_WP_DATE_FORMAT_READABLE, strtotime($delivery_info->delivery_date))."</td>
							<td style='text-align:center'>".open_csa_wp_remove_seconds($delivery_info->delivery_start_time)."</td>
							<td style='text-align:center'>".open_csa_wp_remove_seconds($delivery_info->delivery_end_time)."</td>
							<td style='text-align:center'>$user_in_charge_login</td>
							<td style='text-align:center'
								id = 'open-csa-wp-showDeliveriesOpenOrdersID_$delivery_id'
							>".(($delivery_info->are_orders_open == 1)?"yes":"no")."</td>
							<td style='text-align:center'> 
								<img 
									width='24' height='24'  
									class='delete no-underline' 
									src='$plugins_dir/open-csa-wp/icons/edit.png' 
									style='cursor:pointer;padding-left:10px;' 
									onclick='open_csa_wp_edit_user_order($csaUser->ID, $delivery_id)' 
									title='".__('click to edit this order',OPEN_CSA_WP_DOMAIN)."'/></td>
						";
						
						if ($personal_user_is_responsible === true) {
							if ($user_in_charge_login == "") {
								echo "
									<td style='text-align:center'> 
									<img 
										width='30' height='30'  
										class='delete no-underline' 
										src='$plugins_dir/open-csa-wp/icons/list.png' 
										style='cursor:pointer;padding-left:10px;' 
										onclick='open_csa_wp_become_responsible($csaUser->ID, $delivery_id)' 
										title='".__('click to become responsible for this delivery',OPEN_CSA_WP_DOMAIN)."'/></td>
								";
							} else {
								echo "<td/>";
							}
						} else {
							echo "</tr>";
						}
								
					}
				}
				?>
			</tbody> </table>
		</div>	
<?php
	}
}


function open_csa_wp_show_delivery_orders_list ($personal_user_id, $is_producer, $display) {

	wp_enqueue_script('open-csa-wp-general-scripts');
	wp_enqueue_script('open-csa-wp-deliveries-scripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 
	
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

	global $wpdb;
	if ($personal_user_id != null && $is_producer==false) {
		$deivery_orders_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ". OPEN_CSA_WP_TABLE_DELIVERIES ." WHERE user_in_charge = %d ", $personal_user_id));
	} else {
		$deivery_orders_count = $wpdb->get_var("SELECT COUNT(id) FROM ". OPEN_CSA_WP_TABLE_DELIVERIES);
	}
		
	if ($deivery_orders_count == 0) {
		if ($personal_user_id == null) {
			"<br/><div style='color:brown'>". __('No delivery has yet been initiated.', OPEN_CSA_WP_DOMAIN) ."</div>";
		} else if ($is_producer == false) {
			echo "<br/><div style='color:brown'>". __('You have not yet become responsible for any delivery.', OPEN_CSA_WP_DOMAIN) ."</div>";
		}
	} else {
		if ($personal_user_id) {
			$text_to_show = __("List", OPEN_CSA_WP_DOMAIN);
		} else {
			$text_to_show = __("Delivery Total Orders List", OPEN_CSA_WP_DOMAIN);
		}
		
		if ($is_producer === true) {
			$text_for_label = "showDeliveryOrdersListProducer";
		} else {
			$text_for_label = "showDeliveryOrdersList";
		}
?>		
		<br />
		<div
			id='open-csa-wp-<?php echo $text_for_label; ?>_header'
		>
			<span 
				style="cursor:pointer" 
				id="open-csa-wp-<?php echo $text_for_label; ?>_formHeader_text" 
				onclick="open_csa_wp_toggle_form('<?php echo $text_for_label; ?>','<?php echo $text_to_show;?>', '')">
				<font size='4'>
				<?php 				
					if ($display == false) {
						echo "$text_to_show (".__("show", OPEN_CSA_WP_DOMAIN).")";
					} else {
						echo "$text_to_show (".__("hide", OPEN_CSA_WP_DOMAIN).")";
					}
				?>
				</font>
			</span>
		</div>
		<div id="open-csa-wp-<?php echo $text_for_label; ?>_div" 
			<?php 
				if ($display == false) { 
					echo 'style="display:none"';
				}
			?>	
		>
			<span class='open-csa-wp-tip_order' title='
					<?php _e('Deliveries in "green" are pending and still accept new orders.', OPEN_CSA_WP_DOMAIN)?>
					| <?php _e('Deliveries in "brown" are pending and do not accept new orders.', OPEN_CSA_WP_DOMAIN)?>
					| <?php _e('Deliveries in "grey" are accomplished.', OPEN_CSA_WP_DOMAIN)?>
					| <?php _e('To view the total user orders for some delivery, click the "magnifier" icon.', OPEN_CSA_WP_DOMAIN)?>
				'>
			<p style="color:green;font-style:italic; font-size:13px">
				<?php _e('by pointing here you can read additional information...', OPEN_CSA_WP_DOMAIN)?></p></span>


			<table 
				class='table-bordered' 
				id="open-csa-wp-<?php echo $text_for_label; ?>_table" 
				style='border-spacing:1em' 
			> 
			<thead class='tableHeader'>
				<tr>
					<th><?php _e('Spot', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Order Deadline Date', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Order Deadline Time', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Delivery Date', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Delivery Start Time', OPEN_CSA_WP_DOMAIN)?></th>
					<th><?php _e('Delivery End Time', OPEN_CSA_WP_DOMAIN)?></th>
					<?php 	
						if ($personal_user_id == null || $is_producer === true) {
							echo "<th>".__('User In Charge', OPEN_CSA_WP_DOMAIN)."</th>";
						}
					?>
					<th><?php _e('New Orders Can be Submitted?', OPEN_CSA_WP_DOMAIN)?></th>
					<th/>
				</tr>
			</thead> 
			<tbody> <?php
				global $wpdb;
				$plugins_dir = plugins_url();
				
				if ($personal_user_id == null || $is_producer === true) {
					$deliveries = $wpdb->get_results("SELECT * FROM ". OPEN_CSA_WP_TABLE_DELIVERIES);
				} else {
					$deliveries = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". OPEN_CSA_WP_TABLE_DELIVERIES ." WHERE user_in_charge = %d ", $personal_user_id));
				}
				
				foreach($deliveries as $delivery) 
				{
					$delivery_id = $delivery->id;
					$spot_name = $wpdb->get_var($wpdb->prepare("SELECT spot_name FROM ". OPEN_CSA_WP_TABLE_SPOTS ." WHERE id=%d", $delivery->spot_id));
					 
					$user_in_charge_login = "";
					if ($delivery->user_in_charge != null ) {
						$user_in_charge_login = get_user_by('id', $delivery->user_in_charge)->user_login;
					}
					
					$past_delivery = false;
					$current_date_time = current_time('mysql');
					if (strtotime($delivery->order_deadline_date." ". $delivery->order_deadline_time) < strtotime($current_date_time)) {
						$past_delivery = true;
					}
					
					echo "
						<tr 
							valign='top' 
							id='open-csa-wp-showDeliveriesDeliveryID_$delivery_id'  
							class='open-csa-wp-showDeliveries-delivery'
							style='color:". (($past_delivery === true)?"gray": ($delivery->are_orders_open == 1?"green":"brown")) ."'
						>
						<td style='text-align:center'>$spot_name </td>
						<td style='text-align:center'>".date(OPEN_CSA_WP_DATE_FORMAT_READABLE, strtotime($delivery->order_deadline_date))."</td>
						<td style='text-align:center'>".open_csa_wp_remove_seconds($delivery->order_deadline_time)."</td>
						<td style='text-align:center'>".date(OPEN_CSA_WP_DATE_FORMAT_READABLE, strtotime($delivery->delivery_date))."</td>
						<td style='text-align:center'>".open_csa_wp_remove_seconds($delivery->delivery_start_time)."</td>
						<td style='text-align:center'>".open_csa_wp_remove_seconds($delivery->delivery_end_time)."</td>
						". (($personal_user_id == null || $is_producer === true)?("<td style='text-align:center'>".$user_in_charge_login."</td>"):(""))."
						<td style='text-align:center'
							class='editable_boolean'
							id = 'open-csa-wp-showDeliveriesOpenOrdersID_$delivery_id'
						>".(($delivery->are_orders_open == 1)?"yes":"no")."</td>
						<td style='text-align:center'> 
							<img 
								width='24' height='24'  
								class='delete no-underline' 
								src='$plugins_dir/open-csa-wp/icons/magnifier.png' 
								style='cursor:pointer;padding-left:10px;' 
								onclick='open_csa_wp_request_total_orders_of_delivery(".$delivery_id.", ".
								(($is_producer === true) ? "$personal_user_id" : "null")
								.");' 
								title='".__('click to view the total user orders for this delivery',OPEN_CSA_WP_DOMAIN)."'/></td>
						</tr>

					";
							
				}
				?>
			</tbody> </table>
		</div>	
<?php
	}
}

function open_csa_wp_show_total_orders_of_delivery ($delivery_id, $producer_id, $page_url) {
	global $wpdb;

	$producers_map = open_csa_wp_producers_map_array();
	
	if ($producer_id != null ) {
		$producers_involved = $wpdb->get_col($wpdb->prepare("
								SELECT DISTINCT ".OPEN_CSA_WP_TABLE_PRODUCTS.".producer 
								FROM ".OPEN_CSA_WP_TABLE_PRODUCTS." LEFT JOIN ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS." ON ".OPEN_CSA_WP_TABLE_PRODUCTS.".id = ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".product_id
								WHERE 
									".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".delivery_id = %d AND
									".OPEN_CSA_WP_TABLE_PRODUCTS.".producer = %d
							", $delivery_id, $producer_id));
	} else {
		$producers_involved = $wpdb->get_col($wpdb->prepare("
								SELECT DISTINCT ".OPEN_CSA_WP_TABLE_PRODUCTS.".producer 
								FROM ".OPEN_CSA_WP_TABLE_PRODUCTS." LEFT JOIN ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS." ON ".OPEN_CSA_WP_TABLE_PRODUCTS.".id = ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".product_id
								WHERE ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".delivery_id = %d
								ORDER BY ".OPEN_CSA_WP_TABLE_PRODUCTS.".producer
							", $delivery_id));
	}
	$producers_count = count($producers_involved);
	
	
	if ($producer_id != null) {
		echo "<span class='info-text' style='font-size:15px'>Your orders in ". CsaWpPluginGetReadableDeliveryInfo($delivery_id) ." </span>";
	} else {
		echo "<span class='info-text' style='font-size:15px'>Orders of ". CsaWpPluginGetReadableDeliveryInfo($delivery_id) ." </span>";
	}
	
	if($producers_count <= 0) {
		if ($producer_id == null) {
			echo "<br/><span class='info-text' style='font-size:15px;color:brown'>". __('No order has yet been submitted, for this delivery.', OPEN_CSA_WP_DOMAIN)." </span> <br>";
		} else {
			echo "<br/><span class='info-text' style='font-size:15px;color:brown'>". __('You have no orders in this delivery.', OPEN_CSA_WP_DOMAIN)." </span> <br>";
		}
	} else {	
		$users_count = $wpdb->get_var($wpdb->prepare("
									SELECT COUNT(DISTINCT user_id)
									FROM ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS." 
									WHERE ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".delivery_id = %d 
								", $delivery_id));
								
		$product_details_width = 130;
		$amount_perProduct_width = 30;
		$user_order_width = 58; 			//change also by css (class .left) ???? SHALL WE INCORPORATE THIS? ????
		$product_value_width = 70;
		$main_width = $product_details_width + $amount_perProduct_width + $user_order_width*$users_count + 30; // +30 is an offset to accomodate the space of the first orders
		$table_width = $main_width + $product_value_width;
				
		foreach ($producers_involved as $producer) {
			$revenue = 0; //the amount to be paid to this producer
			echo "<p class='panel'> <span style='font-size:14px'> ". __('Producer', OPEN_CSA_WP_DOMAIN) .": </span> <span class='producer'>".$producers_map[$producer]."</span> </p>";
			
			//Get the products in order for this producer
			$products_in_order = $wpdb->get_results($wpdb->prepare("
										SELECT 
											".OPEN_CSA_WP_TABLE_PRODUCTS.".name,
											".OPEN_CSA_WP_TABLE_PRODUCTS.".variety,
											".OPEN_CSA_WP_TABLE_PRODUCTS.".current_price_in_euro,
											".OPEN_CSA_WP_TABLE_PRODUCTS.".id,
											".OPEN_CSA_WP_TABLE_PRODUCTS.".measurement_unit, 
											SUM(".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".quantity) AS total, 
											FORMAT(SUM(".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".quantity*".OPEN_CSA_WP_TABLE_PRODUCTS.".current_price_in_euro),2) as costPerProduct
										FROM ".OPEN_CSA_WP_TABLE_PRODUCTS." LEFT JOIN ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS." ON ".OPEN_CSA_WP_TABLE_PRODUCTS.".id = ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".product_id
										WHERE 
											".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".delivery_id = %d
											AND ".OPEN_CSA_WP_TABLE_PRODUCTS.".producer = %d
										GROUP BY ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".product_id 
									", $delivery_id, $producer));
									
			//display product details
			foreach ($products_in_order as $product) 
			{
				$orders_of_product = $wpdb->get_results($wpdb->prepare("
											SELECT 
												".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".user_id, 
												".OPEN_CSA_WP_TABLE_PRODUCTS.".name,
												".OPEN_CSA_WP_TABLE_PRODUCTS.".variety,
												".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".quantity
											FROM ".OPEN_CSA_WP_TABLE_PRODUCTS." LEFT JOIN ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS." ON ".OPEN_CSA_WP_TABLE_PRODUCTS.".id = ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".product_id
											WHERE 
												".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".delivery_id = %d AND
												".OPEN_CSA_WP_TABLE_PRODUCTS.".id = %d
										", $delivery_id, $product->	id));
				
				$total = $product->total;
				if (strpos($total, '.') !== FALSE ) {
					$total = number_format((float)$product->total, 1, '.', '');
				}
				?>
				<div class='container' style='min-width:100%;width:<?php echo $table_width ?>px;font-size:14px'>  <!--keeps the quantity and total value divs together-->
					
						<div class='container' style='float:left; width:<?php echo $main_width ?>px;'> 
							<!--product details-->
							
							<div class='left' style='width:<?php echo $product_details_width ?>px; background-color:Khaki; display:block; '>
								<div style='display:table-cell; vertical-align:middle; height:70px; padding-left:10px '>
								<?php echo $product->name." ".$product->variety."<br/> <span class='info-text'>(".$product->current_price_in_euro." € / ".$product->measurement_unit.")</span>" ?>
								</div>
							</div>
							
							<div class='left' style='width:<?php echo $amount_perProduct_width ?>px;height:70px;line-height:70px;padding-left:10px;font-weight:bold;font-size:16px;background-color:Khaki'>
								<span style='display:inline-block; vertical-align:middle;'><?php echo $total ?></span>
							</div>

							<!--order details-->
							<div class="div_tr">
								<?php
								foreach($orders_of_product as $product_order) 
									echo "<div class='left' style='font-weight:bold;'>".mb_substr(get_user_by('id', $product_order->user_id)->user_login, 0, 7,'UTF-8')."</div>"; 
								?>
							</div>
								<!--<div style="height:37px;"></div>-->
							<div class="div_tr">
								<?php
								foreach($orders_of_product as $preOrder) 
									echo "<div class='left' >".$product_order->quantity."</div>"; 
								?>
							</div>
													
						</div>
					
						<!-- price details-->
						<div class='left' style='display:table; height:70px;'>
							<div style='display:table-cell; vertical-align:bottom;'>
							<?php echo "<span class='info-text'>".$product->costPerProduct."€</span>"; ?>
							</div>
						</div>
					
					
				</div>
				
				<?php $revenue += $product->costPerProduct;
			}
			
			echo"<br/><p class='total' style='margin: -2% 0 3% 0'><span class='emphasis-box'>". __('Total',OPEN_CSA_WP_DOMAIN).": ".$revenue." € </span></p><br/>";
		}
	
		?>
		<table style="margin-top:30px" class='table-straight'> <!-- stucture table (to display the following table next to each other -->
			<tr><td>
				<span style="font-weight:bold;color:green"><?php _e('Cost per consumer',OPEN_CSA_WP_DOMAIN); ?></span><?php
				
				//Show costs in total
				$total_user_costs = $wpdb->get_results($wpdb->prepare("
												SELECT 
													".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".user_id,
													FORMAT(SUM(".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".quantity*".OPEN_CSA_WP_TABLE_PRODUCTS.".current_price_in_euro),2) AS cost
												FROM ".OPEN_CSA_WP_TABLE_PRODUCTS." LEFT JOIN ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS." ON ".OPEN_CSA_WP_TABLE_PRODUCTS.".id = ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".product_id
												WHERE 
													".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".delivery_id = %d
												GROUP BY ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".user_id
											", $delivery_id));
				$total_cost = $wpdb->get_var($wpdb->prepare("
												SELECT FORMAT(SUM(".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".quantity*".OPEN_CSA_WP_TABLE_PRODUCTS.".current_price_in_euro),2)
												FROM ".OPEN_CSA_WP_TABLE_PRODUCTS." LEFT JOIN ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS." ON ".OPEN_CSA_WP_TABLE_PRODUCTS.".id = ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".product_id
												WHERE 
													".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".delivery_id = %d
											", $delivery_id));
				?>
				<table style='width:280px;' class='table-bordered'>
					<thead>
						<tr>
							<td><?php _e('Consumer',OPEN_CSA_WP_DOMAIN); ?></td>
							<td><?php _e('Total Cost',OPEN_CSA_WP_DOMAIN); ?></td>
						</tr>
					</thead>
					
					<?php 
					foreach ($total_user_costs as $tCost) { 
					?>
						<tr>
							<td style='width:200px;font-weight:bold'><?php echo open_csa_wp_user_readable(get_user_by('id', $tCost->user_id)) ?></td>
							<td><?php echo round($tCost->cost, 1)." €"; ?></td>
						</tr>
					<?php 
					} 
					?>

					<tr style='background-color:Khaki'>
						<td style="font-weight:bold"><?php _e('Total',OPEN_CSA_WP_DOMAIN); ?></td>
						<td style="font-weight:bold"><?php echo round($total_cost,1)." €"; ?></td>
					</tr>
					</tr>
				</table>
			</td>
			<td> <div style='width:80px'>  </div> </td>
			<td>
				<?php	
				
				//Show costs per producer
				?><span style="font-weight:bold;color:green;margin-left:5%">Cost per producer</span><?php
				$producer_costs = $wpdb->get_results($wpdb->prepare("
															SELECT 
																".OPEN_CSA_WP_TABLE_PRODUCTS.".producer,
																FORMAT(SUM(".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".quantity*".OPEN_CSA_WP_TABLE_PRODUCTS.".current_price_in_euro),2) AS cost
															FROM 
																(".OPEN_CSA_WP_TABLE_PRODUCTS." LEFT JOIN ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS." ON ".OPEN_CSA_WP_TABLE_PRODUCTS.".id = ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".product_id)
															WHERE 
																".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS.".delivery_id = %d
															GROUP BY ".OPEN_CSA_WP_TABLE_PRODUCTS.".producer
														", $delivery_id));
				
						
				?>


				<table style='width:200px;' class='table-bordered'>
					<thead>
						<tr>
							<td><?php _e('Procuder',OPEN_CSA_WP_DOMAIN); ?></td>
							<td style='width:100px'><?php _e('Income',OPEN_CSA_WP_DOMAIN); ?></td>
						</tr>
					</thead>
					<?php
					foreach ($producer_costs as $p_cost) {
						echo "
							<tr>
								<td style='width:200px;font-weight:bold'>".$producers_map[$p_cost->producer]."</td>
								<td>". round($p_cost->cost, 1)." €</td>
							</tr>";
					
						}
					?>

					<tr style='background-color:Khaki'>
						<td style="font-weight:bold"><?php _e('Total',OPEN_CSA_WP_DOMAIN); ?></td>
						<td style="font-weight:bold"><?php echo round($total_cost,1)." €" ?></td>
					</tr>
				</table>

			</td>
			</tr>
		</table>
		
		<br>
		<table class='table-bordered'>
			<?php
				$user_orders_comments = $wpdb->get_results($wpdb->prepare("
											SELECT user_id, comments 
											FROM ". OPEN_CSA_WP_TABLE_USER_ORDERS ." 
											WHERE 
												delivery_id = %d AND
												comments IS NOT NULL
										", $delivery_id));

			if (count($user_orders_comments) > 0){
					echo "<span style='font-weight:bold;color:green'>Comments:</span>";
					foreach ($user_orders_comments as $user_order_comments)
						echo "<tr><td style='font-weight:bold;width:200px'>".open_csa_wp_user_readable_without_login(get_user_by('id', $user_order_comments->user_id))."</td><td>".$user_order_comments->comments."</td></tr>";
				}
			?>
		</table>
								
	<?php
	}
	?>
	<input 
	type="button"
	class="button button-secondary"
	value='<?php _e('back to orders...',OPEN_CSA_WP_DOMAIN); ?>'
	onclick='window.location.replace(" <?php echo $page_url ?> ");'
	/>	

	
<?php
}


add_action( 'wp_ajax_open-csa-wp-add_new_or_update_order', 'open_csa_wp_add_new_or_update_order' );

function open_csa_wp_add_new_or_update_order() {

	if( isset($_POST['data']) && isset($_POST['delivery_id']) && isset($_POST['user_id']) && isset($_POST['current_user_id'])) {

		$delivery_id = intval(open_csa_wp_clean_input($_POST['delivery_id']));
		$user_id = intval(open_csa_wp_clean_input($_POST['user_id']));
		$data = json_decode(stripslashes($_POST['data']),true);
		$num_of_products = count($data)-1;
		$current_user_id = intval(open_csa_wp_clean_input($_POST['current_user_id']));

		if (open_csa_wp_is_deadline_reached($delivery_id) === true &&	
				(open_csa_wp_is_user_csa_administrator($current_user_id) === false &&
				open_csa_wp_is_user_responsible_for_delivery($current_user_id, $delivery_id) === false)
			) {
			echo 'skipped, deadline reached and user is either not an administrator or not responsible for this delivery';
		} else {		
			//insert product orders
			$success = true;
			for ($i=0; $i<$num_of_products-1; $i+=3) {
				$product_id = $data[$i+2]['value'];
				$quantity = $data[$i+1]['value'];
				
				if($quantity>0) {								
					global $wpdb;

					$product_order_info = $wpdb->get_results($wpdb->prepare("
									SELECT *
									FROM ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS."
									WHERE 
										delivery_id = %d AND
										user_id = %d AND
										product_id = %d
								",$delivery_id, $user_id, $product_id));
					
					if (count($product_order_info) > 0) {
						if ($wpdb->update(
								OPEN_CSA_WP_TABLE_PRODUCT_ORDERS,
								array('quantity' => $product_order_info[0]->quantity + $quantity), 
								array(
									'delivery_id' => $delivery_id,
									'user_id' => $user_id,
									'product_id' => $product_id
								)
						) === false) {
						$success = false;
						}
					}
					else if( $wpdb->insert(
								OPEN_CSA_WP_TABLE_PRODUCT_ORDERS,
								array(
									'delivery_id'	=> $delivery_id,
									'user_id'		=> $user_id,
									'product_id' 	=> $product_id,
									'quantity'		=> $quantity
								), 
								array ("%d", "%d", "%d", "%d")
						) === FALSE) {
						$success = false;
					}
				}
			}
		
		
			if ($success === false) {
				echo 'error, some sql request failed';
			} else {
				echo 'success, product orders added.';
			}
			
			
			$already_exist = $wpdb->get_var($wpdb->prepare("
					SELECT COUNT(delivery_id) 
					FROM ".OPEN_CSA_WP_TABLE_USER_ORDERS."
					WHERE 
						delivery_id = %d AND
						user_id = %d
				",$delivery_id, $user_id));
			
			//insert user order
			if($already_exist == 0) {
				if ($wpdb->insert(
						OPEN_CSA_WP_TABLE_USER_ORDERS,
						array(
							'delivery_id'	=> $delivery_id,
							'user_id'		=> $user_id,
							'comments'		=> $data[$i]['value']
						), 
						array ("%d", "%d", "%s")
				) === FALSE) {
					echo 'error, sql request failed';
				}
				else {
					echo 'success, user order added.';
				}
			}
			else {
				if ($wpdb->insert(
						OPEN_CSA_WP_TABLE_USER_ORDERS,
						array(
							'comments'		=> $data[$i]['value']
						),
						array(
							'delivery_id'	=> $delivery_id,
							'user_id'		=> $user_id,
						), 
						array ("%s")
				) === FALSE) {
					echo 'error, sql request failed';
				}
				else {
					echo 'success, user order comments updated.';
				}
			}
		}
	}
	else echo 'error,Bad request.';

	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_open-csa-wp-update_user_order_product_quantity', 'open_csa_wp_update_user_order_product_quantity' );

function open_csa_wp_update_user_order_product_quantity() {
	if(isset($_POST['value']) && isset($_POST['delivery_id']) && isset($_POST['user_id']) && isset($_POST['product_id']) && isset($_POST['current_user_id'])) {
		$new_value = open_csa_wp_clean_input($_POST['value']);
		$delivery_id = intval(open_csa_wp_clean_input($_POST['delivery_id']));
		$user_id = intval(open_csa_wp_clean_input($_POST['user_id']));
		$product_id = intval(open_csa_wp_clean_input($_POST['product_id']));
		$current_user_id = intval(open_csa_wp_clean_input($_POST['current_user_id']));
		
		if(!empty($delivery_id) && !empty($user_id) && !empty($product_id) && !empty($current_user_id)) {
			if (open_csa_wp_is_deadline_reached($delivery_id) === true &&	
					(open_csa_wp_is_user_csa_administrator($current_user_id) === false &&
					open_csa_wp_is_user_responsible_for_delivery($current_user_id, $delivery_id) === false)
				) {
				echo 'skipped, user is either not an administrator or not responsible for this delivery';
			} else {
				// Updating the information 
				global $wpdb;

				if(	$wpdb->update(
					OPEN_CSA_WP_TABLE_PRODUCT_ORDERS,
					array('quantity' => $new_value), 
					array(
						'delivery_id' => $delivery_id,
						'user_id' => $user_id,
						'product_id' => $product_id
					)
				) === FALSE) {
					echo 'error, sql request failed';												
				} else {
					echo 'success';
				}
			}
		} 
		else echo 'error,Empty values: ';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_open_csa_wp_delete_user_product_order', 'open_csa_wp_delete_user_product_order' );

function open_csa_wp_delete_user_product_order() {
	if(isset($_POST['delivery_id']) && isset($_POST['user_id']) && isset($_POST['product_id']) && isset($_POST['current_user_id'])) {
		$delivery_id = intval(open_csa_wp_clean_input($_POST['delivery_id']));
		$user_id = intval(open_csa_wp_clean_input($_POST['user_id']));
		$product_id = intval(open_csa_wp_clean_input($_POST['product_id']));
		$current_user_id = intval(open_csa_wp_clean_input($_POST['current_user_id']));
					
		if(!empty($delivery_id) && !empty($user_id) && !empty($product_id) && !empty($current_user_id)) {
			if (open_csa_wp_is_deadline_reached($delivery_id) === true &&	
					(open_csa_wp_is_user_csa_administrator($current_user_id) === false &&
					open_csa_wp_is_user_responsible_for_delivery($current_user_id, $delivery_id) === false)
				) {
				echo 'skipped, user is either not an administrator or not responsible for this delivery';
			} else {
				// Updating the information 
				global $wpdb;

				if(	$wpdb->delete(
					OPEN_CSA_WP_TABLE_PRODUCT_ORDERS,
					array(
						'delivery_id' => $delivery_id,
						'user_id' => $user_id,
						'product_id' => $product_id
					),
					array ('%d', '%d', '%d')
				) === FALSE) {
					echo 'error, sql request failed';												
				} else {
					echo 'success';
				}
			} 
		}
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_open-csa-wp-delete_user_order', 'csa_wp_plugiin_delete_user_order' );

function csa_wp_plugiin_delete_user_order() {
	if(isset($_POST['delivery_id']) && isset($_POST['user_id']) && isset($_POST['current_user_id'])) {
		if(!empty($_POST['delivery_id']) && !empty($_POST['user_id']) && !empty($_POST['current_user_id'])) {
			
			$delivery_id = intval(open_csa_wp_clean_input($_POST['delivery_id']));
			$user_id = intval(open_csa_wp_clean_input($_POST['user_id']));
			$current_user_id = intval(open_csa_wp_clean_input($_POST['current_user_id']));			

			if (open_csa_wp_is_deadline_reached($delivery_id) === true &&	
					(open_csa_wp_is_user_csa_administrator($current_user_id) === false &&
					open_csa_wp_is_user_responsible_for_delivery($current_user_id, $delivery_id) === false)
				) {
				echo 'skipped, deadline reached and user is either not an administrator or not responsible for this delivery';
			} else {
				$success = true;
				global $wpdb;
				
				if(	$wpdb->delete(
						OPEN_CSA_WP_TABLE_PRODUCT_ORDERS,
						array(
							'delivery_id' => $delivery_id,
							'user_id' => $user_id
						),
						array ('%d', '%d')
					) === FALSE) {
					$success = false;
				}
				
				if(	$wpdb->delete(
						OPEN_CSA_WP_TABLE_USER_ORDERS,
						array(
							'delivery_id' => $delivery_id,
							'user_id' => $user_id
						),
						array ('%d', '%d')
					) === FALSE) {
					$success = false;
				}

				if ($success === true) {
					echo 'success, user order deleted';
				} else {
					echo 'error, sql request failed';										
				}
			}
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_open-csa-wp-update_user_order_comments', 'open_csa_wp_update_user_order_comments' );

function open_csa_wp_update_user_order_comments() {
	if(isset($_POST['delivery_id']) && isset($_POST['user_id']) && isset($_POST['comments'])) {
		$delivery_id = intval(open_csa_wp_clean_input($_POST['delivery_id']));
		$user_id = intval(open_csa_wp_clean_input($_POST['user_id']));
		$comments = open_csa_wp_clean_input($_POST['comments']);
		
		if(!empty($delivery_id) && !empty($user_id) && !empty($comments)) {
			global $wpdb;
		
			// Updating the information 
			if(	$wpdb->update(
				OPEN_CSA_WP_TABLE_USER_ORDERS,
				array('comments' => $comments), 
				array(
					'delivery_id' => $delivery_id,
					'user_id' => $user_id
				)
			) === FALSE) {
				echo 'error, sql request failed';												
			} else {
				echo 'success';
			}
		} 
		else echo 'error,Empty values: ';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}


add_action( 'wp_ajax_open-csa-wp-become_responsible', 'open_csa_wp_become_responsible' );

function open_csa_wp_become_responsible() {
	if(isset($_POST['delivery_id']) && isset($_POST['user_id'])) {
		$delivery_id = intval(open_csa_wp_clean_input($_POST['delivery_id']));
		$user_id = intval(open_csa_wp_clean_input($_POST['user_id']));
		
		if(!empty($delivery_id) && !empty($user_id)) {
			global $wpdb;
			$user_in_charge = $wpdb->get_var($wpdb->prepare("SELECT user_in_charge FROM ".OPEN_CSA_WP_TABLE_DELIVERIES." WHERE id=%d", $delivery_id));
		
			if ($user_in_charge != null) {
				echo 'skipped, responsible user already exists';
			} else {
				// Updating the information 
				if(	$wpdb->update(
					OPEN_CSA_WP_TABLE_DELIVERIES,
					array('user_in_charge' => $user_id), 
					array(
						'id' => $delivery_id,
					)
				) === FALSE) {
					echo 'error, sql request failed';												
				} else {
					echo 'success';
				}
			}
		} 
		else echo 'error,Empty values: ';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

?>
