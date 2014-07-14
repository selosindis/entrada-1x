<div id="entrada-navigation" class="panel" summary="Entrada">
    <div class="panel-head">
        <h3>Entrada</h3>
    </div>
</div>
<div class="clearfix panel-body">
        {if $is_logged_in}
            {$entrada_navigation}
        {else}
        <ul class="menu">
            <li><a href="{$sys_website_url}">Log In</a></li>
        </ul>
        {/if}
</div>
