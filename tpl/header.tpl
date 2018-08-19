<div class="header-container">
    <header class="left padding-tiny header-logo">
        <a href="{$home}" class="left">
            {if $logo != "" && $logo != $home}
                <img src="{$logo}" alt="logo do site {$sitename}"
                     title="{$sitename} {($sitesub != "") ? " - $sitesub" : ""}" class="col" height="39"
                     style="height: 39px;width: auto">
                <h1 class="padding-0" style="font-size:0">{$sitename}</h1>
            {elseif $favicon && $favicon != $home}
                <img src="{$favicon}" class="left padding-right" height="35" style="height: 35px">
                <h1 class="font-xlarge padding-0 left">{$sitename}</h1>
            {else}
                <h1 class="font-xlarge padding-0">{$sitename}</h1>
            {/if}
        </a>
    </header>
    <nav class="right padding-tiny" role="navigation">
        <ul class="header-nav">
            {$menu}
            {if $loged}
                <li class="left padding-0">
                    <a href="{$home}dashboard" class="right padding-medium">minha conta</a>
                </li>
                <li class="left padding-0 pointer">
                        <span onclick="logoutDashboard()"
                              class="right padding-medium opacity hover-opacity-off">SAIR
                        </span>
                </li>
            {else}
                <li class="left padding-0">
                    <a href="{$home}login" class="right padding-medium">login</a>
                </li>
            {/if}

            <li id="open-menu" onclick="toggleSidebar()">
                <div class="menu icon" data-before="menu" data-after="remove"></div>
            </li>
        </ul>
    </nav>
</div>