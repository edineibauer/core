    <div class="col padding-medium theme color-grayscale-min" id="main-header-app-sidebar">
        <div class="col padding-medium perfil-sidebar">
            {if $loged}
                {if $login.imagem}
                    <img src="{$home}image/{$login.imagem}&h=100&w=100" height="80" width="80"
                         class="radius-circle margin-bottom z-depth-2" id="app-sidebar-img-perfil">
                {else}
                    <div id="app-sidebar-img-perfil" class="col s4"><i class="material-icons font-jumbo">people</i></div>
                {/if}
                <div class="col font-large font-bold">
                    {$login.nome}
                </div>
                <div class="col font-medium font-light">
                    <span class="left">
                        {$login.email}
                    </span>
                    <button id="btn-editLogin" style="margin-top: -13px"
                            class="right color-white opacity z-depth-0 border hover-opacity-off radius padding-small color-grey-light">
                        <i class="material-icons left font-large">edit</i>
                        <span class="left" style="padding-right: 5px">perfil</span>
                    </button>
                </div>
            {else}
                <i id="app-sidebar-img-perfil" class="material-icons font-jumbo margin-bottom">people</i>
                <div class="app-sidebar-name">
                    Anônimo
                </div>
            {/if}
        </div>
    </div>

    <div class="col" id="main-app-sidebar">
        <ul class="col" id="applications"></ul>
        {* <ul class="col border-bottom padding-bottom" id="actions">
             <li class="col pointer color-hover-grey-light">
                 <a href="{$home}dashboard" class="col padding-small padding-16">
                     <i class="material-icons left padding-right font-xlarge">notifications</i>
                     <span class="left padding-tiny">Notificações</span>
                 </a>
             </li>
         </ul>*}

        <ul class="col border-top" id="menu">
            {$menu}
            {if $loged}
                <li class="col pointer color-hover-grey-light">
                    <a href="{$home}dashboard" class="col padding-large opacity hover-opacity-off">
                        Minha Conta
                    </a>
                </li>
                <li class="col pointer color-hover-grey-light">
                    <span onclick="logoutDashboard()" class="col padding-large opacity hover-opacity-off">
                        sair
                    </span>
                </li>
            {else}
                <li class="col pointer color-hover-grey-light">
                    <a href="{$home}login" class="col padding-large">login</a>
                </li>
            {/if}
        </ul>
    </div>