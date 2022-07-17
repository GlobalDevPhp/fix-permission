<?php
/**
 * WP-Sweep admin.php
 *
 * @package fix-permission
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$FP = FixPermission::get_instance();
$logs = $paths = $statuses = '';
if (!empty($FP->loop_obj)) {
    $statuses = $FP->loop_obj->getStatuses();
    $paths = $FP->loop_obj->getPaths();
    $logs = implode(PHP_EOL, $FP->loop_obj->log);
}
$selected_flag = '0764';
if (isset($_POST['permission_flag']))
    $selected_flag = $_POST['permission_flag'];
?>
<div class="wrap">
    <h2><?php esc_html_e( 'Fix file permissions', 'fix-permission' ); ?></h2>
    <p><?php esc_html_e( 'Please enter paths, which you want to apply the action. You can set relative and absolute paths, but only for the current site folder.', 'fix-permission' ); ?></p>
    <p><?php esc_html_e( 'In order for the changes to be applied, you must turn off the test mode. In test mode, the plugin will look all the specified folders and files, and in the case of a recursive traversal, nested ones, but will not delete or change them.', 'fix-permission' ); ?></p>
    <form method="post" class="" action="?page=fix-permission">
        <?php wp_nonce_field('fix-permission-nonce', 'generated_nonce'); ?>
        <div class="paths_area">
            <textarea name="fperm_options_paths" rows="20" cols="80"><?= !empty($paths) ? stripslashes($paths) : 'wp-content/cache
wp-content/cache/index.html'; ?></textarea>
            <textarea rows="10" cols="30" id="test_area" disabled="disabled"><?php echo $statuses; ?></textarea>
            <textarea rows="10" cols="30" id="test_area" wrap="off" disabled="disabled"><?php echo $logs; ?></textarea>
        </div>            
        <div>
            <p><?php esc_html_e( 'Select type the action:', 'fix-permission' ); ?></p>
            <select id="type_action" name="action_type">
                <option value="permission" <?php echo (!empty($_POST['action_type']) && $_POST['action_type'] == 'permission')?'selected="selected"':'';?>><?php esc_html_e( 'Edit permission', 'fix-permission' ); ?></option>
                <option value="deletion" <?php echo (!empty($_POST['action_type']) && $_POST['action_type'] == 'deletion')?'selected="selected"':'';?>><?php esc_html_e( 'Deleting', 'fix-permission' ); ?></option>
            </select>
            
            <select id="perm_mode" name="permission_flag">
                <?php foreach (UpdatePermission::$permission_flags AS $name => $mod):?>                
                    <option value="<?php echo $name; ?>" <?php echo $name == $selected_flag? 'selected="selected"' : ''; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
            <select id="type_action" name="test_mode">
                <option value="true" <?php echo (!empty($_POST['test_mode']) && $_POST['test_mode'] == 'true')?'selected="selected"':'';?>><?php esc_html_e( 'Test mode On', 'fix-permission' ); ?></option>
                <option value="false" <?php echo (!empty($_POST['test_mode']) && $_POST['test_mode'] == 'false')?'selected="selected"':'';?>><?php esc_html_e( 'Test mode Off', 'fix-permission' ); ?></option>
            </select>
        </div>
        <div> 
            <p><?php esc_html_e( 'You want to perform an action on subfolders and files?', 'fix-permission' ); ?></p>
            <label for="recursion_on"><?php esc_html_e( 'Apply action recursively?', 'fix-permission' ); ?></label>                        
            <input type="checkbox" id="recursion_on" name="recursion_on" value="yes" <?php echo !empty($_POST['recursion_on'])?'checked="checked"':'';?>>

        </div>
        <div>
            <p></p>
            <button type="submit" class="button button-primary btn-test"><?php esc_html_e( 'Run action', 'fix-permission' ); ?></button>
        </div>            
    </form>
</div>
<style>
    .paths_area {
        display: flex;
    }
    h5.error {
        color: red;
    }
</style>
<script>
    document.getElementById('type_action').onchange = function () {
        // if value is category id
        if (this.value === 'permission') {
            document.getElementById('perm_mode').removeAttribute("disabled");
        } else
            document.getElementById('perm_mode').setAttribute("disabled","disabled");
    }
</script>
