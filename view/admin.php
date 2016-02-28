<img class="illustration" src="<?php echo ipFileUrl('Plugin/Composer/assets/icon.png') ?>" alt="Composer"/>

<div class="commandCenter ipsCommandCenter">
    <p>
        <a href="#" data-command="install" class="ipsComposerCommand btn btn-new" title="The main command. You should use it for sure">Install</a>
        <a href="#" data-command="update" class="ipsComposerCommand btn btn-default" title="If you don't specify library version in config, Composer will look if there are any updates available.">Update</a>
        <a href="#" data-command="clearCache" class="ipsComposerCommand btn btn-default" title="Something don't work as expected? Try to clear the cache.">Clear cache</a>
    </p>
    <p>All these commands will be passed directly to the composer as if you run them from the command line.</p>
    <hr/>
    <h2>Current configuration (file/secure/Composer/composer.json)</h2>
    <p>
        <a href="#" class="ipsEditComposerJson btn btn-new" title="Edit the composer.json file">Edit</a>
        <a href="#" class="ipsSaveComposerJson btn btn-new hidden" title="Edit the composer.json file">Save</a>
        <a href="#" class="ipsCancelComposerJson btn btn-default hidden" title="Edit the composer.json file">Cancel</a>
    </p>
    <pre class="ipsComposerJsonPreview composerJsonPreview"><?php echo esc($composerJson)  ?></pre>
    <?php echo $form ?>
</div>
<div class="loader hidden ipsLoader">
    <p>This may take several minutes...</p>
</div>

<?php echo ipView('responseModal.php'); ?>
