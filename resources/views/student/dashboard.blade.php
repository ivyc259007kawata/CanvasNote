<x-app-layout>


    <x-slot name="header">

        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            CanvasNote
        </h2>

    </x-slot>


    <div class="p-6">

        <h1 class="text-2xl font-bold">
            生徒ホーム
        </h1>

        <p class="mt-4">
            生徒としてログインしています。
        </p>

        <p class="mt-2">
            ようこそ、{{ auth()->user()->name }} さん
        </p>


        {{-- ログアウト --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-6">

            @csrf

            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                ログアウト
            </button>

        </form>

    </div>


</x-app-layout>