@props(['insights'])

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-bold text-gray-800">
            ⚠️ Flight Risk Monitor
            <span class="text-xs font-normal text-gray-500 ml-2">(AI Powered)</span>
        </h3>
    </div>

    <div class="p-0">
        @forelse($insights as $insight)
            <div class="p-4 border-b border-gray-50 hover:bg-gray-50 transition">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center gap-3">
                        <img src="{{ $insight->employee->profile_image_url ?? 'https://ui-avatars.com/api/?name=' . $insight->employee->full_name }}"
                            class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm">{{ $insight->employee->full_name }}</h4>
                            <p class="text-xs text-gray-500">{{ $insight->employee->job_title }}</p>
                        </div>
                    </div>
                    <span
                        class="px-2 py-1 rounded text-xs font-bold
                        {{ $insight->risk_level === 'critical' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ $insight->score }}% Risk
                    </span>
                </div>

                <!-- The Factors (PHP Logic) -->
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach ($insight->factors as $factor)
                        <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-200">
                            {{ $factor }}
                        </span>
                    @endforeach
                </div>

                <!-- The AI Advice -->
                @if ($insight->ai_analysis_uz)
                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                        <div class="flex gap-2">
                            <span class="text-lg">🤖</span>
                            <p class="text-xs text-blue-800 italic leading-relaxed">
                                "{{ $insight->ai_analysis_uz }}"
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-gray-400 text-sm">
                No high-risk employees detected. Good job! 🎉
            </div>
        @endforelse
    </div>
</div>
