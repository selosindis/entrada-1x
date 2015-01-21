<div id="community-my-membership" class="panel">
    <div class="panel-head">
        <h3>This Community</h3>
    </div>
    <div class="clearfix panel-body">
        <span class="content-small">My Membership</span>
        <ul class="menu">
            <li class="community">
                <a href="{$sys_website_url}/profile">{$member_name}</a>
                <br>
                {$date_joined}
            </li>
        </ul>
        <ul class="menu">
            <li class="on">
                <a href="{$sys_website_url}/communities?section=leave&amp;community={$community_id}">Quit This Community</a>
            </li>
        </ul>
        <hr>
        <ul class="menu">
            <li class="community">
                <a href="{$site_community_url}:members">View All Members</a>
            </li>
        </ul>
    </div>
</div>
