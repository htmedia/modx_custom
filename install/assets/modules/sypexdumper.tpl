// <?php 
/**
 * Sypex Dumper
 * 
 * Backup and restore MySQL
 * 
 * @category	module
 * @version 	1.0
 * @internal	@modx_category Manager and Admin
 * @internal    @installset base, sample
 */

if(!$modx->hasPermission('bk_manager')) {
                $e->setError(3);
                $e->dumpError();
}

if ($manager_theme)
        $manager_theme .= '/';
else    $manager_theme  = '';

echo '<h1>Sypex Dumper 2</h1>
<div class="sectionHeader">Backup and restore MySQL</div>
<div class="sectionBody" id="lyr4">
<iframe src="../sxd/" width="586" height="462" frameborder="0" style="margin:0;"></iframe>
</div>';

