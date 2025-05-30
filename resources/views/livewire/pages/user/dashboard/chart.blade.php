<?php

use App\Models\Transaction;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new class extends Component {
    public $chartData;
    
    public function mount()
    {
        $this->loadChartData();
    }

    public function loadChartData()
    {
        try {
            $user = Auth::user();
            $transactions = Transaction::where('user_id', $user->id)
                ->where('name', '!=', 'Initial Account Balance')
                ->orderBy('created_at')
                ->get();

            $groupedData = $transactions->groupBy(function($transaction) {
                return Carbon::parse($transaction->created_at)->format('Y-m');
            });

            $chartData = [];
            foreach ($groupedData as $period => $items) {
                $chartData[] = [
                    'period' => Carbon::createFromFormat('Y-m', $period)->format('M Y'),
                    'income' => $items->where('type_id', 1)->sum('amount'),
                    'expenses' => $items->where('type_id', 2)->sum('amount')
                ];
            }

            $this->chartData = array_values($chartData);
        } catch (\Exception $e) {
            $this->chartData = [];
        }
    }
}; ?>

<div class="card p-6 bg-base-100 shadow-lg border border-base-200 mt-4 hover:shadow-xl transition-all duration-300">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">Monthly Income vs Expenses</h2>
    </div>
    
    <div class="w-full h-[300px]" x-data="{
        chart: null,
        init() {
            this.initChart();
            this.$wire.$watch('chartData', () => {
                this.updateChart();
            });
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
            
            // Create gradient fills
            const incomeGradient = ctx.createLinearGradient(0, 0, 0, 300);
            incomeGradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)'); // Blue
            incomeGradient.addColorStop(1, 'rgba(59, 130, 246, 0.2)');
            
            const expenseGradient = ctx.createLinearGradient(0, 0, 0, 300);
            expenseGradient.addColorStop(0, 'rgba(236, 72, 153, 0.8)'); // Pink
            expenseGradient.addColorStop(1, 'rgba(236, 72, 153, 0.2)');

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @js(array_column($chartData, 'period')),
                    datasets: [
                        {
                            label: 'Income',
                            data: @js(array_column($chartData, 'income')),
                            backgroundColor: incomeGradient,
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1,
                            borderRadius: 4,
                            barPercentage: 0.6
                        },
                        {
                            label: 'Expenses',
                            data: @js(array_column($chartData, 'expenses')),
                            backgroundColor: expenseGradient,
                            borderColor: 'rgb(236, 72, 153)',
                            borderWidth: 1,
                            borderRadius: 4,
                            barPercentage: 0.6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 750,
                        easing: 'easeInOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString('en-PH');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += '₱' + context.parsed.y.toLocaleString('en-PH');
                                    return label;
                                }
                            }
                        },
                        legend: {
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 13
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
            
            this.chart.data.labels = @js(array_column($chartData, 'period'));
            this.chart.data.datasets[0].data = @js(array_column($chartData, 'income'));
            this.chart.data.datasets[1].data = @js(array_column($chartData, 'expenses'));
            this.chart.update('none');
        }
    }" 
    wire:ignore>
        <canvas x-ref="canvas"></canvas>
    </div>
</div> 