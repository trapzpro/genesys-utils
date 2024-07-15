<div class="p-6 bg-white rounded-lg shadow-md">
    <form wire:submit.prevent="syncOrgs">
        <div class="mb-4">
            <label for="sourceOrg" class="block text-sm font-medium text-gray-700">Source Organization</label>
            <select id="sourceOrg" wire:model="sourceOrg"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Select an organization</option>
                @foreach ($organizations as $key => $org)
                    <option value="{{ $key }}">{{ $org }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label for="destinationOrg" class="block text-sm font-medium text-gray-700">Destination Organization</label>
            <select id="destinationOrg" wire:model="destinationOrg"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Select an organization</option>
                @foreach ($organizations as $key => $org)
                    <option value="{{ $key }}">{{ $org }}</option>
                @endforeach
            </select>
        </div>


        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Items to Sync</label>
            @foreach ($items as $item => $subitems)
                <div class="mt-2">
                    <div class="flex items-center">
                        <input type="checkbox" wire:click="toggleItem('{{ $item }}')"
                            @if (in_array($item, $selectedItems)) checked @endif
                            class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                        <label class="ml-2 block text-sm leading-5 text-gray-900">{{ ucfirst($item) }}</label>
                    </div>
                    @if (in_array($item, $selectedItems))
                        <div class="ml-6 mt-1">
                            @foreach ($subitems as $subitemKey => $subitem)
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="selectedItems" value="{{ $subitemKey }}"
                                        class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                    <label
                                        class="ml-2 block text-sm leading-5 text-gray-900">{{ $subitem }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($sourceOrg && $destinationOrg && !empty($selectedItems))
            <div>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Sync Organizations
                </button>
            </div>
        @endif
    </form>

    Source: {{ $sourceOrg }}
    Destination: {{ $destinationOrg }}
    @if ($sourceOrg && $destinationOrg)
        <div>
            <button type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Sync Organizations
            </button>
        </div>
    @endif



    @if (session()->has('message'))
        <div class="mt-4 p-4 bg-green-200 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif
</div>
