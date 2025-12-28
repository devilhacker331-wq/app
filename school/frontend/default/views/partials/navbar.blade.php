<header class="header-part">
    <div class="container">
        <div class="header-content">
            <button type="button" class="header-menu">
                <i class="lni lni-menu"></i>
            </button>
            <a href="{{ base_url('/') }}" class="header-logo"><img
                    src="{{ base_url('uploads/images/' . frontendData::get_backend('photo')) }}" alt="logo"></a>
            <nav class="header-nav">
                <div class="header-nav-group">
                    <a href="{{ base_url('/') }}" class="header-nav-logo"><img
                            src="{{ base_url('uploads/images/' . frontendData::get_backend('photo')) }}"
                            alt="logo"></a>
                    <button type="button" class="header-nav-close lni lni-close"></button>
                </div>
                <ul class="header-nav-list">
                    @if (isset($menu['frontendTopbarMenus']))
                        @foreach ($menu['frontendTopbarMenus'] as $frontendTopbarMenu)
                        @if ($frontendTopbarMenu['menu_typeID'] == '3')
                            <li
                                class="header-nav-item">
                                <a href="{{ $frontendTopbarMenu['menu_link'] }}"
                                    class="header-nav-link">{{ $frontendTopbarMenu['menu_label'] }}</a></li>
                                    @else
                                    
                                    <li
                                class="header-nav-item {{ $_SERVER['REQUEST_URI'] == '/frontend/page/' . $fpages[$frontendTopbarMenu['menu_pagesID']]->url ? 'active' : '' }}">
                                <a href="{{ base_url('frontend/page/' . $fpages[$frontendTopbarMenu['menu_pagesID']]->url) }}"
                                    class="header-nav-link">{{ $frontendTopbarMenu['menu_label'] }}</a></li>
                                    
                                    @endif
                        @endforeach
                    @endif 
                </ul>
            </nav>
            <a href="{{ base_url('signin') }}" class="header-action-btn"
                style="-webkit-mask-image: url('{{ base_url('frontend/default/assets/images/badge.png') }}');" >
                <img src="{{ base_url('frontend/default/assets/images/icon.png') }}" alt="icon">
                <span> {{ (bool) $this->session->userdata("loggedin") ? 'dashboard' : 'login'}} </span>
                
            </a>

        </div>
    </div>
</header>