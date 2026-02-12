
if (typeof window.nexoraDashboardData === 'undefined') {
    console.log('Dashboard data not found');
    return;
}
document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('Nexora Service Suite-dashboard');
    initializeCharts();
    initializeGauges();
    enhanceHoverEffects();
    
    console.log('Modern Dashboard initialized successfully');
});
function initializeCharts() {
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: window.nexoraDashboardData.salesChart.labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: window.nexoraDashboardData.salesChart.revenue,
                        borderColor: '#6C5DD3',
                        backgroundColor: 'rgba(108, 93, 211, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6C5DD3',
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    },
                    {
                        label: 'Services',
                        data: window.nexoraDashboardData.salesChart.services,
                        borderColor: '#00E5FF',
                        backgroundColor: 'rgba(0, 229, 255, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#00E5FF',
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(26, 31, 43, 0.95)',
                        backdropFilter: 'blur(20px)',
                        titleColor: '#FFFFFF',
                        bodyColor: '#CBD5E0',
                        borderColor: 'rgba(108, 93, 211, 0.3)',
                        borderWidth: 1,
                        cornerRadius: 12,
                        padding: 16
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            borderColor: 'rgba(255, 255, 255, 0.2)'
                        },
                        ticks: {
                            color: '#CBD5E0',
                            font: {
                                family: 'Inter, sans-serif',
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            borderColor: 'rgba(255, 255, 255, 0.2)'
                        },
                        ticks: {
                            color: '#CBD5E0',
                            font: {
                                family: 'Inter, sans-serif',
                                size: 11
                            },
                            callback: function(value) {
                                return '€' + (value / 1000) + 'k';
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
    const usersCtx = document.getElementById('usersChart');
    if (usersCtx) {
        const usersChart = new Chart(usersCtx, {
            type: 'bar',
            data: {
                labels: window.nexoraDashboardData.usersChart.labels,
                datasets: [{
                    label: 'Active Users',
                    data: window.nexoraDashboardData.usersChart.data,
                    backgroundColor: 'rgba(108, 93, 211, 0.8)',
                    borderColor: '#6C5DD3',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(26, 31, 43, 0.95)',
                        backdropFilter: 'blur(20px)',
                        titleColor: '#FFFFFF',
                        bodyColor: '#CBD5E0',
                        borderColor: 'rgba(108, 93, 211, 0.3)',
                        borderWidth: 1,
                        cornerRadius: 12,
                        padding: 16
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            borderColor: 'rgba(255, 255, 255, 0.2)'
                        },
                        ticks: {
                            color: '#CBD5E0',
                            font: {
                                family: 'Inter, sans-serif',
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)',
                            borderColor: 'rgba(255, 255, 255, 0.2)'
                        },
                        ticks: {
                            color: '#CBD5E0',
                            font: {
                                family: 'Inter, sans-serif',
                                size: 11
                            },
                            callback: function(value) {
                                return (value / 1000) + 'k';
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart',
                    delay: function(context) {
                        return context.dataIndex * 100;
                    }
                }
            }
        });
    }
}
function initializeGauges() {
    const satisfactionGauge = document.querySelector('.gauge[data-value="95"]');
    if (satisfactionGauge) {
        const gaugeFill = satisfactionGauge.querySelector('.gauge-fill');
        if (gaugeFill) {
            setTimeout(() => {
                gaugeFill.style.width = '95%';
            }, 500);
        }
    }
    const safetyGauge = document.querySelector('.gauge-fill.green');
    if (safetyGauge) {
        setTimeout(() => {
            safetyGauge.style.width = '93%';
        }, 800);
    }
    const progressBars = document.querySelectorAll('.progress-fill');
    progressBars.forEach((bar, index) => {
        setTimeout(() => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        }, 1000 + (index * 200));
    });
}
function enhanceHoverEffects() {
    const cards = document.querySelectorAll('.stat-card, .welcome-card, .satisfaction-card, .referral-card, .chart-card, .projects-card, .orders-card');
    
    cards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            card.style.setProperty('--mouse-x', x + 'px');
            card.style.setProperty('--mouse-y', y + 'px');
        });
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 0 30px rgba(108, 93, 211, 0.6), 0 8px 32px rgba(0, 0, 0, 0.3)';
            this.style.borderColor = 'rgba(108, 93, 211, 0.4)';
            const icon = this.querySelector('.stat-icon, .order-icon');
            if (icon) {
                icon.style.animation = 'float 2s ease-in-out infinite';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 8px 32px rgba(0, 0, 0, 0.3)';
            this.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            const icon = this.querySelector('.stat-icon, .order-icon');
            if (icon) {
                icon.style.animation = 'none';
            }
        });
    });
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('body::before, body::after');
        
        parallaxElements.forEach(element => {
            const speed = 0.5;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
}
function animateNumbers() {
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(statValue => {
        const finalNumber = parseInt(statValue.textContent.replace(/[^\d]/g, ''));
        if (isNaN(finalNumber)) return;
        
        const duration = 2000;
        const step = finalNumber / (duration / 16);
        let currentNumber = 0;
        
        const timer = setInterval(function() {
            currentNumber += step;
            if (currentNumber >= finalNumber) {
                currentNumber = finalNumber;
                clearInterval(timer);
            }
            const originalText = statValue.textContent;
            const formattedNumber = originalText.replace(/\d+/, Math.floor(currentNumber));
            statValue.textContent = formattedNumber;
        }, 16);
    });
}

function updateDashboardStats() {
    console.log('Updating dashboard stats...');
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach(card => {
        card.classList.add('loading');
    });
    setTimeout(() => {
        cards.forEach(card => {
            card.classList.remove('loading');
        });
    }, 2000);
}
window.nexoraDashboard = {
    initializeCharts,
    initializeGauges,
    enhanceHoverEffects,
    animateNumbers,
    updateDashboardStats
};
setInterval(updateDashboardStats, 30000);
function showLoadingState() {
    const cards = document.querySelectorAll('.stat-card, .welcome-card, .satisfaction-card, .referral-card, .chart-card, .projects-card, .orders-card');
    cards.forEach(card => {
        card.classList.add('loading');
    });
}

function hideLoadingState() {
    const cards = document.querySelectorAll('.stat-card, .welcome-card, .satisfaction-card, .referral-card, .chart-card, .projects-card, .orders-card');
    cards.forEach(card => {
        card.classList.remove('loading');
    });
}
function handleResponsive() {
    const sidebar = document.querySelector('.dashboard-sidebar');
    const main = document.querySelector('.dashboard-main');
    const menuToggle = document.createElement('button');
    menuToggle.className = 'mobile-menu-toggle';
    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    menuToggle.style.display = 'none';
    
    document.querySelector('.dashboard-topbar').prepend(menuToggle);
    
    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('open');
    });
    document.addEventListener('click', function(e) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
    function updateMobileMenu() {
        if (window.innerWidth <= 768) {
            menuToggle.style.display = 'block';
        } else {
            menuToggle.style.display = 'none';
            sidebar.classList.remove('open');
        }
    }
    
    window.addEventListener('resize', updateMobileMenu);
    updateMobileMenu();
}
document.addEventListener('DOMContentLoaded', handleResponsive); 