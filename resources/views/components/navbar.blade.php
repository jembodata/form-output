<nav class="bg-white border-b border-gray-200 shadow-sm mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex space-x-8 items-center">
                <div class="flex-shrink-0 flex items-center font-bold text-lg text-gray-900 mr-4">
                    {{ config('app.name') }}
                </div>
                
                <div class="flex space-x-6 h-full">
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition {{ request()->routeIs('home') ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Output vs Plan
                    </a>

                    <a href="{{ route('quality.manage') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition {{ request()->routeIs('quality.manage') ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Scrap & Defect
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>