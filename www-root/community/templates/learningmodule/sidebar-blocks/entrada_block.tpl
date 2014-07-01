<section>
	<h1>Entrada<span class="corner"></span></h1>
	<ul>
		{if $is_logged_in}
        <li><a href="{$sys_website_url}/dashboard">Dashboard</a></li>
        <li><a href="{$sys_website_url}/communities">Communities</a></li>
        <li><a href="{$sys_website_url}/courses">Courses</a></li>
        <li><a href="{$sys_website_url}/events">Learning Events</a></li>
        <li><a href="{$sys_website_url}/search">Curriculum Search</a></li>
        <li><a href="{$sys_website_url}/people">People Search</a></li>
        <li><a href="{$sys_website_url}/library">Library</a></li>
        <li><a href="{$sys_website_url}?action=logout">Log Out</a></li>
        {else}
        <li><a href="{$sys_website_url}">Log In</a></li>
        {/if}
	</ul>
</section>
