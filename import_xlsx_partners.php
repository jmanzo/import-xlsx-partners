<?php   
/* 
Plugin Name: Import XLSX Files Partnerships
Plugin URI: https://about.me/jeanmanzo
Description: Import Partners from XLSX Files 
Author: Jean Manzo
Version: 1.0 
Author URI: https://about.me/jeanmanzo
*/  

require_once __DIR__ . '/vendor/simplexlsx.class.php';  

class IXLSXFilesPartnerships 
{
	private $success = '';
	private $error = '';
	private $filename = '';

	/**
	 * Save posts
	 */
	private function saveCustomPostType($postarr) {
		foreach($postarr as $value) {
			$item_categories = explode(', ', $value['Expertise']);
			$item_locations = $value['Region'];

			$post = array(
				'post_title'    => wp_strip_all_tags( $value['AccountNameLegalName'] ),
				'post_content'  => $value['PartnerWebsiteDescription'],
				'post_status'   => 'draft',
				'post_type'		=> 'item',
				'post_author'   => get_current_user_id(),
				'meta_input' 	=> array(
					'jv_item_address' => $value['PhysicalStreet'],
					'jv_item_phone'	=> $value['Phone'],
					'jv_item_email' => $value['Phone'],
					'jv_item_website' => $value['Website'],
					'jv_item_lat' => $value['GeocodeLatitude'],
					'jv_item_lng' => $value['GeocodeLongitude'],

				)
			);

			$post_id = wp_insert_post( $post );

			wp_set_object_terms($post_id, $item_categories, 'item_category', true);
			wp_set_object_terms($post_id, $item_locations, 'item_location', true);
		}

		return true;
	}

	/**
	 *	File extension validator
	 */
	private function validateXlsx($file_ext) {
		if ( $file_ext == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ) {
			return true;
		}
		return false;
	}

	/**
	*	Main functionality and front
	*/
	public function main() {
		if ($_FILES) {
			$validator = $this->validateXlsx($_FILES['xlsx_import']['type']);
			
			if ($validator) {*/
				$file = $_FILES['xlsx_import']['tmp_name'];
				//$file = __DIR__ . '/example-file.xlsx';
				$xlsx = new SimpleXLSX($file);

				if ( $xlsx->success() ) {
					$data = $xlsx->rows();
					$arrayFields = array();
					// Get header fields
					for ($i=0; $i < count($data[0]); $i++) {
						for ($j=0; $j < count($data)-1; $j++) {
							$index = $j + 1;
							$key = preg_replace('([^A-Za-z0-9])', '', $data[0][$i]);
							// Get content fields
							$arrayFields[$j][$key] = $data[$index][$i];
						}
					}

					if ($this->saveCustomPostType($arrayFields)) {
						$this->success = "File imported successfully.";
					} else {
						$this->error = "Has occurred an error with the process import. Please, contact the administrator.";
					}
				} else {
					echo $xlsx->error();
				}
			} else {
				$this->error = 'Wrong file extension. Please, upload a xlsx (Excel) file';
			}
		}
		?>
		<div class="wrap">
			<h2>
				Import XLS Files
			</h2>
			<br/>
			
			<?php if($this->error !== '') :  ?>
				<div class="error">
			    	<?php echo $this->error; ?>
				</div>
			<?php else: ?>
				<div class="success">
			    	<?php echo $this->success; ?>
				</div>
			<?php endif; ?>

			<form method="post" enctype="multipart/form-data">
				<!-- File input -->
				<p>
					<label for="xlsx_import">
						Upload a XLSX file:
					</label><br/>
					<input name="xlsx_import" id="xlsx_import" type="file" value=""/>
				</p>
				<p class="submit">
					<input type="submit" class="button" name="submit" value="Import" />
				</p>
			</form>
		</div>
		<?php
	}
}

function ixlsx_admin_actions() {  
	$plugin = new IXLSXFilesPartnerships;
    add_management_page("Import Partners", "Import XLSX Files","manage_options","import_xlsx_partners", array($plugin, 'main'));  
}  
  
add_action('admin_menu', 'ixlsx_admin_actions');


