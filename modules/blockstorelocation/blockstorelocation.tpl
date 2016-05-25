<!-- Block mymodule -->
<div id="block_store_location" class="block">
    <h4>Nous trouver</h4>
    <div class="block_content">
        <p>Hello,
            {if isset($address) && $address}
                {$address}
            {else}
                Aucune adresse définie.
            {/if}
            !
        </p>
    </div>
</div>
<!-- /Block mymodule -->