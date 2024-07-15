<div class="p-6 bg-white rounded-lg shadow-md">
    <form wire:submit.prevent="retrieveUsers">
        <div class="mb-4">
            <label for="clientId" class="block text-sm font-medium text-gray-700">Client ID</label>
            <input id="clientId" wire:model="clientId" type="text"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
        </div>
        <div class="mb-4">
            <label for="clientSecret" class="block text-sm font-medium text-gray-700">Client Secret</label>
            <input id="clientSecret" wire:model="clientSecret" type="password"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
        </div>
        <div>
            <button wire:click="retrieveUsers"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Retrieve Users
            </button>
        </div>
    </form>

    @if (!empty($users))
        <div class="mt-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Users</h3>
            <ul class="mt-2">
                @foreach ($users as $user)
                    <li class="mt-1 text-sm text-gray-600">{{ $user['name'] }} -{{ $user['state'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session()->has('message'))
        <div class="mt-4 p-4 bg-green-200 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif
</div>
