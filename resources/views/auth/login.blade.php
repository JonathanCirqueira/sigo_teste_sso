<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Acesse sua conta utilizando o provedor centralizado da empresa.') }}
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-col gap-4">
        <a href="{{ route('sso.redirect') }}" 
           class="inline-flex justify-center items-center w-full px-4 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('Entrar com Sigo SSO') }}
        </a>
    </div>
</x-guest-layout>
