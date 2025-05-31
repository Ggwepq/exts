<?php

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public $chartData = [];

    public function mount()
    {
        $this->loadChartData();
    }

    public function loadChartData()
    {
        try {
            $user = Auth::user();

            $accounts = Account::where('user_id', $user->id)->get();

            $this->chartData = $accounts
                ->map(function ($account) {
                    return [
                        'name' => $account->name,
                        'balance' => (float) $account->balance,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            $this->chartData = [];
        }
    }
};
?>

<div class="card p-6 bg-base-100 shadow-lg border border-base-200 mt-4 hover:shadow-xl transition-all duration-300">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">Wallets Breakdown</h2>
    </div>

    <div class="w-full h-[300px]" x-data="{
        chart: null,
        init() {
            this.initChart();
            this.$wire.$watch('chartData', () => this.updateChart());
        },
        destroyChart() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
        },
        initChart() {
            this.destroyChart();
            const ctx = this.$refs.canvas.getContext('2d');

            this.chart = new Chart(ctx, {
                type: 'bar', // Change to 'bar' if you prefer
                data: {
                    labels: @js(array_column($chartData, 'name')),
                    datasets: [{
                        data: @js(array_column($chartData, 'balance')),
                        backgroundColor: [
                            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#14B8A6'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                font: { size: 14 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ₱${context.parsed.toLocaleString('en-PH')}`;
                                }
                            }
                        }
                    }
                }
            });
        },
        updateChart() {
            if (!this.chart) {
                this.initChart();
                return;
            }

            this.chart.data.labels = @js(array_column($chartData, 'name'));
            this.chart.data.datasets[0].data = @js(array_column($chartData, 'balance'));
            this.chart.update();
        }
    }" wire:ignore>
        <canvas x-ref="canvas"></canvas>
    </div>
</div>
