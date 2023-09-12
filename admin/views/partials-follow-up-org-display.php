<?php

/**
 * The admin area of the Follow-up to load the New Org table
 */
?>
<div class="wrap">    
    <h2>Follow-up New Organizations</h2>
        <div id="nds-wp-list-table-demo">           
            <div id="nds-post-body">        
                <!-- <form id="nds-user-list-form" method="get"> -->
                <div id="mvab-followup-org-table">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                    <?php 
                        
                        $follow_up_table->display(); 
                    ?>                  
                </div>
                <!-- </form> -->
            </div>          
        </div>
</div>