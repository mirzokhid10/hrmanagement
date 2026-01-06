<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="dropdown-item has-icon text-danger">
        {{ __('Log Out') }}
    </button>
</form>
