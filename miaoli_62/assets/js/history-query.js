// 歷史資料查詢 JavaScript

let currentPage = 1;
let currentData = null;
let allLocations = [];
let currentChart = null; // 當前圖表實例
let currentChartType = 'value'; // 當前顯示的圖表類型（for傾斜儀：'value'或'battery'）

// 頁面載入時初始化
document.addEventListener('DOMContentLoaded', function () {
    loadLocations();
    setupEventListeners();
    setDefaultDateRange();
});

// 設定預設時間範圍（最近7天）
function setDefaultDateRange() {
    const now = new Date();
    const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);

    document.getElementById('end-time').value = formatDateTimeLocal(now);
    document.getElementById('start-time').value = formatDateTimeLocal(weekAgo);
}

// 格式化日期為 datetime-local 格式
function formatDateTimeLocal(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// 載入所有點位
async function loadLocations() {
    try {
        const response = await fetch('api/get_locations.php');
        const locations = await response.json();
        allLocations = locations;
    } catch (error) {
        console.error('載入點位失敗:', error);
    }
}

// 設定事件監聽
function setupEventListeners() {
    document.getElementById('category-select').addEventListener('change', onCategoryChange);
    document.getElementById('location-select').addEventListener('change', onLocationChange);
}

// 分類改變時
function onCategoryChange() {
    const category = document.getElementById('category-select').value;
    const locationSelect = document.getElementById('location-select');
    const sensorSelect = document.getElementById('sensor-select');

    // 重置後續選單
    locationSelect.innerHTML = '<option value="">請選擇場域</option>';
    sensorSelect.innerHTML = '<option value="">請先選擇場域</option>';
    sensorSelect.disabled = true;
    hideResults();

    if (!category) {
        locationSelect.disabled = true;
        return;
    }

    // 特殊處理恆聖工程系統
    if (category === '恆聖工程系統') {
        // 恆聖工程系統只有一個固定場域
        const option = document.createElement('option');
        option.value = 'hengshen';
        option.textContent = '縣道149甲線25K~26K';
        locationSelect.appendChild(option);
        locationSelect.disabled = false;
        return;
    }

    // 過濾該分類的點位
    const filteredLocations = allLocations.filter(loc => loc.category === category);

    if (filteredLocations.length === 0) {
        locationSelect.disabled = true;
        return;
    }

    // 填充場域選單
    filteredLocations.forEach(loc => {
        const option = document.createElement('option');
        option.value = loc.id;
        option.textContent = loc.name;
        locationSelect.appendChild(option);
    });

    locationSelect.disabled = false;
}

// 場域改變時
async function onLocationChange() {
    const locationId = document.getElementById('location-select').value;
    const sensorSelect = document.getElementById('sensor-select');

    sensorSelect.innerHTML = '<option value="">請選擇感測器</option>';
    sensorSelect.disabled = true;
    hideResults();

    if (!locationId) {
        return;
    }

    // 特殊處理恆聖工程系統
    if (locationId === 'hengshen') {
        // 恆聖工程系統的三種感測器
        const hengshenSensors = [
            { id: 'hengshen_tilt', name: '電子式雙軸傾斜計', type: 'hengshen_tilt' },
            { id: 'hengshen_load', name: '地錨荷重計', type: 'hengshen_load' },
            { id: 'hengshen_strain', name: '鋼梁應變計', type: 'hengshen_strain' }
        ];

        hengshenSensors.forEach(sensor => {
            const option = document.createElement('option');
            option.value = sensor.id;
            option.textContent = sensor.name;
            option.dataset.type = sensor.type;
            sensorSelect.appendChild(option);
        });

        sensorSelect.disabled = false;
        return;
    }

    try {
        // 獲取該場域的所有感測器（直接查詢sensor表）
        const response = await fetch(`api/get_sensors_by_location.php?location_id=${locationId}`);
        const result = await response.json();

        if (!result.success || !result.sensors) {
            return;
        }

        const sensors = result.sensors;

        // 填充感測器選單
        sensors.forEach(sensor => {
            const option = document.createElement('option');
            option.value = sensor.id;
            option.textContent = sensor.chinese_name || sensor.name;
            option.dataset.type = sensor.type;
            sensorSelect.appendChild(option);
        });

        sensorSelect.disabled = false;

    } catch (error) {
        console.error('載入感測器失敗:', error);
    }
}

// 查詢資料
async function queryData(page = 1) {
    const sensorSelect = document.getElementById('sensor-select');
    const sensorId = sensorSelect.value;
    const startTime = document.getElementById('start-time').value;
    const endTime = document.getElementById('end-time').value;

    if (!sensorId) {
        alert('請選擇感測器');
        return;
    }

    if (!startTime || !endTime) {
        alert('請選擇時間範圍');
        return;
    }

    // 顯示載入中
    showLoading();

    try {
        // 檢查是否為恆聖工程系統
        const isHengshen = sensorId.startsWith('hengshen_');
        const apiUrl = isHengshen ? 'api/get_hengshen_history.php' : 'api/get_history_data.php';

        const url = `${apiUrl}?sensor_id=${sensorId}&start_time=${encodeURIComponent(startTime)}&end_time=${encodeURIComponent(endTime)}&page=${page}`;
        const response = await fetch(url);
        const result = await response.json();

        if (!result.success) {
            alert('查詢失敗: ' + result.error);
            hideResults();
            return;
        }

        currentData = result;
        currentPage = page;
        displayResults(result);

    } catch (error) {
        console.error('查詢失敗:', error);
        alert('查詢失敗，請稍後再試');
        hideResults();
    }
}

// 顯示結果
function displayResults(data) {
    const resultsContainer = document.getElementById('results-container');
    const resultsCount = document.getElementById('results-count');
    const tableContainer = document.getElementById('results-table-container');
    const exportBtn = document.getElementById('export-btn');
    const chartBtn = document.getElementById('chart-btn');
    const chartContainer = document.getElementById('chart-container');

    resultsContainer.style.display = 'block';
    exportBtn.style.display = data.records.length > 0 ? 'inline-block' : 'none';

    // 顯示/隱藏圖表按鈕（只對可繪製圖表的感測器類型顯示）
    const chartableTypes = ['tilt', 'rain', 'water', 'battery', 'power', 'hengshen_tilt', 'hengshen_load', 'hengshen_strain'];
    if (data.records.length > 0 && chartableTypes.includes(data.sensor.type)) {
        chartBtn.style.display = 'inline-block';
        chartBtn.textContent = '顯示圖表';
        chartContainer.classList.remove('active');
    } else {
        chartBtn.style.display = 'none';
        chartContainer.classList.remove('active');
    }

    // 更新筆數
    resultsCount.textContent = `共 ${data.pagination.total} 筆資料（第 ${data.pagination.page} / ${data.pagination.total_pages} 頁）`;

    if (data.records.length === 0) {
        tableContainer.innerHTML = '<div class="no-data">查無資料</div>';
        document.getElementById('pagination-container').innerHTML = '';
        chartBtn.style.display = 'none';
        chartContainer.classList.remove('active');
        return;
    }

    // 生成表格
    const table = generateTable(data.sensor.type, data.records);
    tableContainer.innerHTML = table;

    // 生成分頁
    generatePagination(data.pagination);
}

// 生成表格（根據感測器類型）
function generateTable(sensorType, records) {
    let headerHtml = '';
    let bodyHtml = '';

    switch (sensorType) {
        case 'tilt':
            // 傾斜儀：時間、數值、電池電壓
            headerHtml = `
                <tr>
                    <th>資料時間</th>
                    <th>數值</th>
                    <th>電池電壓 (V)</th>
                </tr>
            `;
            bodyHtml = records.map(r => `
                <tr>
                    <td>${r.trigger_time}</td>
                    <td>${r.value !== null && r.value !== '' ? r.value : '-'}</td>
                    <td>${r.battery_voltage !== null && r.battery_voltage !== '' ? r.battery_voltage : '-'}</td>
                </tr>
            `).join('');
            break;

        case 'power':
            // 市電：時間、狀態
            headerHtml = `
                <tr>
                    <th>資料時間</th>
                    <th>市電狀態</th>
                </tr>
            `;
            bodyHtml = records.map(r => `
                <tr>
                    <td>${r.trigger_time}</td>
                    <td>${r.power_status} (${r.value})</td>
                </tr>
            `).join('');
            break;

        case 'detection':
            // 滯留物辨識：時間、圖片
            headerHtml = `
                <tr>
                    <th>資料時間</th>
                    <th>圖片預覽</th>
                </tr>
            `;
            bodyHtml = records.map(r => {
                if (r.image_url) {
                    return `
                        <tr>
                            <td>${r.trigger_time}</td>
                            <td>
                                <img src="${r.image_url}" 
                                     class="image-thumbnail" 
                                     onclick="showImageModal('${r.image_url}')"
                                     onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 fill=%22%23666%22>無圖片</text></svg>'"
                                     alt="滯留物圖片">
                            </td>
                        </tr>
                    `;
                } else {
                    return `
                        <tr>
                            <td>${r.trigger_time}</td>
                            <td>無圖片</td>
                        </tr>
                    `;
                }
            }).join('');
            break;

        case 'viber':
            // 震動感測器（落石）：時間、狀態
            headerHtml = `
                <tr>
                    <th>資料時間</th>
                    <th>偵測狀態</th>
                </tr>
            `;
            bodyHtml = records.map(r => `
                <tr>
                    <td>${r.trigger_time}</td>
                    <td>${r.rockfall || '偵測到落石'}</td>
                </tr>
            `).join('');
            break;

        case 'hengshen_tilt':
            // 恆聖 - 電子式雙軸傾斜計
            headerHtml = `
                <tr>
                    <th>資料時間</th>
                    <th>傾斜計1 X軸 (sec)</th>
                    <th>傾斜計1 Y軸 (sec)</th>
                    <th>傾斜計2 X軸 (sec)</th>
                    <th>傾斜計2 Y軸 (sec)</th>
                </tr>
            `;
            bodyHtml = records.map(r => `
                <tr>
                    <td>${r.data_time}</td>
                    <td>${r.TI_1X !== null ? r.TI_1X : '-'}</td>
                    <td>${r.TI_1Y !== null ? r.TI_1Y : '-'}</td>
                    <td>${r.TI_2X !== null ? r.TI_2X : '-'}</td>
                    <td>${r.TI_2Y !== null ? r.TI_2Y : '-'}</td>
                </tr>
            `).join('');
            break;

        case 'hengshen_load':
            // 恆聖 - 地錨荷重計
            headerHtml = `
                <tr>
                    <th>資料時間</th>
                    <th>荷重計1 (Tone)</th>
                    <th>荷重計2 (Tone)</th>
                    <th>荷重計3 (Tone)</th>
                </tr>
            `;
            bodyHtml = records.map(r => `
                <tr>
                    <td>${r.data_time}</td>
                    <td>${r.LC_1 !== null ? r.LC_1 : '-'}</td>
                    <td>${r.LC_2 !== null ? r.LC_2 : '-'}</td>
                    <td>${r.LC_3 !== null ? r.LC_3 : '-'}</td>
                </tr>
            `).join('');
            break;

        case 'hengshen_strain':
            // 恆聖 - 鋼梁應變計
            headerHtml = `
                <tr>
                    <th>資料時間</th>
                    <th>應變計1 上 (με)</th>
                    <th>應變計1 下 (με)</th>
                    <th>應變計2 上 (με)</th>
                    <th>應變計2 下 (με)</th>
                </tr>
            `;
            bodyHtml = records.map(r => `
                <tr>
                    <td>${r.data_time}</td>
                    <td>${r.VG_1UP !== null ? r.VG_1UP : '-'}</td>
                    <td>${r.VG_1DN !== null ? r.VG_1DN : '-'}</td>
                    <td>${r.VG_2UP !== null ? r.VG_2UP : '-'}</td>
                    <td>${r.VG_2DN !== null ? r.VG_2DN : '-'}</td>
                </tr>
            `).join('');
            break;

        default:
            // 其他感測器：時間、數值
            headerHtml = `
                <tr>
                    <th>資料時間</th>
                    <th>數值</th>
                </tr>
            `;
            bodyHtml = records.map(r => `
                <tr>
                    <td>${r.trigger_time}</td>
                    <td>${r.value}</td>
                </tr>
            `).join('');
            break;
    }

    return `
        <table class="data-table">
            <thead>${headerHtml}</thead>
            <tbody>${bodyHtml}</tbody>
        </table>
    `;
}

// 生成分頁
function generatePagination(pagination) {
    const container = document.getElementById('pagination-container');

    if (pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    // 上一頁
    html += `<button class="pagination-btn" ${pagination.page === 1 ? 'disabled' : ''} onclick="queryData(${pagination.page - 1})">上一頁</button>`;

    // 頁碼
    const startPage = Math.max(1, pagination.page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.page + 2);

    if (startPage > 1) {
        html += `<button class="pagination-btn" onclick="queryData(1)">1</button>`;
        if (startPage > 2) {
            html += `<span>...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="pagination-btn ${i === pagination.page ? 'active' : ''}" onclick="queryData(${i})">${i}</button>`;
    }

    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) {
            html += `<span>...</span>`;
        }
        html += `<button class="pagination-btn" onclick="queryData(${pagination.total_pages})">${pagination.total_pages}</button>`;
    }

    // 下一頁
    html += `<button class="pagination-btn" ${pagination.page === pagination.total_pages ? 'disabled' : ''} onclick="queryData(${pagination.page + 1})">下一頁</button>`;

    container.innerHTML = html;
}

// 重置篩選器
function resetFilters() {
    document.getElementById('category-select').value = '';
    document.getElementById('location-select').innerHTML = '<option value="">請先選擇分類</option>';
    document.getElementById('location-select').disabled = true;
    document.getElementById('sensor-select').innerHTML = '<option value="">請先選擇場域</option>';
    document.getElementById('sensor-select').disabled = true;
    setDefaultDateRange();
    hideResults();
}

// 隱藏結果
function hideResults() {
    document.getElementById('results-container').style.display = 'none';
    document.getElementById('export-btn').style.display = 'none';
    currentData = null;
}

// 顯示載入中
function showLoading() {
    const tableContainer = document.getElementById('results-table-container');
    tableContainer.innerHTML = '<div class="loading">查詢中...</div>';
    document.getElementById('results-container').style.display = 'block';
}

// 顯示圖片放大模態框
function showImageModal(imageUrl) {
    const modal = document.getElementById('image-modal');
    const modalImage = document.getElementById('modal-image');
    modalImage.src = imageUrl;
    modal.classList.add('active');
}

// 關閉圖片模態框
function closeImageModal() {
    const modal = document.getElementById('image-modal');
    modal.classList.remove('active');
}

// 匯出 Excel
async function exportToExcel() {
    if (!currentData || !currentData.records.length) {
        alert('沒有資料可匯出');
        return;
    }

    const sensorSelect = document.getElementById('sensor-select');
    const sensorId = sensorSelect.value;
    const startTime = document.getElementById('start-time').value;
    const endTime = document.getElementById('end-time').value;

    // 檢查是否為恆聖工程系統
    const isHengshen = sensorId.startsWith('hengshen_');
    const apiUrl = isHengshen ? 'api/export_hengshen_excel.php' : 'api/export_history_excel.php';

    // 開啟新視窗下載
    const url = `${apiUrl}?sensor_id=${sensorId}&start_time=${encodeURIComponent(startTime)}&end_time=${encodeURIComponent(endTime)}`;
    window.open(url, '_blank');
}
// ========== 圖表功能 ==========

// 切換圖表顯示/隱藏
function toggleChart() {
    const chartContainer = document.getElementById('chart-container');
    const chartBtn = document.getElementById('chart-btn');

    if (chartContainer.classList.contains('active')) {
        chartContainer.classList.remove('active');
        chartBtn.textContent = '顯示圖表';
    } else {
        chartContainer.classList.add('active');
        chartBtn.textContent = '隱藏圖表';
        renderChart();
    }
}

// 渲染圖表
function renderChart(chartType = null) {
    if (!currentData || !currentData.records || currentData.records.length === 0) {
        return;
    }

    const sensorType = currentData.sensor.type;

    // 如果指定了chartType，更新當前類型
    if (chartType) {
        currentChartType = chartType;
    }

    // 銷毀舊圖表
    if (currentChart) {
        currentChart.destroy();
        currentChart = null;
    }

    const canvas = document.getElementById('data-chart');
    if (!canvas) {
        console.error('Canvas element not found');
        return;
    }

    // 清除 canvas
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // 根據感測器類型設置圖表選項按鈕
    setupChartOptions(sensorType);

    // 準備圖表資料
    const chartData = prepareChartData(sensorType);

    if (!chartData) {
        return;
    }

    // 創建圖表
    currentChart = new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: '#e0e0e0'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        displayFormats: {
                            hour: 'MM/DD HH:mm',
                            day: 'MM/DD'
                        }
                    },
                    ticks: {
                        color: '#e0e0e0'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                y: {
                    beginAtZero: false,
                    ticks: {
                        color: '#e0e0e0'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            }
        }
    });
}

// 設置圖表選項按鈕
function setupChartOptions(sensorType) {
    const optionsContainer = document.getElementById('chart-options');
    optionsContainer.innerHTML = '';

    if (sensorType === 'tilt') {
        // 傾斜儀有兩個選項：數值和電池電壓
        const valueBtn = document.createElement('button');
        valueBtn.className = 'chart-option-btn' + (currentChartType === 'value' ? ' active' : '');
        valueBtn.textContent = '數值';
        valueBtn.onclick = () => {
            document.querySelectorAll('.chart-option-btn').forEach(b => b.classList.remove('active'));
            valueBtn.classList.add('active');
            renderChart('value');
        };

        const batteryBtn = document.createElement('button');
        batteryBtn.className = 'chart-option-btn' + (currentChartType === 'battery' ? ' active' : '');
        batteryBtn.textContent = '電池電壓';
        batteryBtn.onclick = () => {
            document.querySelectorAll('.chart-option-btn').forEach(b => b.classList.remove('active'));
            batteryBtn.classList.add('active');
            renderChart('battery');
        };

        optionsContainer.appendChild(valueBtn);
        optionsContainer.appendChild(batteryBtn);
    }
}

// 準備圖表數據
function prepareChartData(sensorType) {
    const records = currentData.records;
    const sensorName = currentData.sensor.chinese_name || currentData.sensor.name;

    // 根據感測器類型準備不同的數據
    switch (sensorType) {
        case 'tilt':
            if (currentChartType === 'battery') {
                // 電池電壓圖表
                return {
                    labels: records.map(r => new Date(r.trigger_time)),
                    datasets: [{
                        label: '電池電壓 (V)',
                        data: records.map(r => r.battery_voltage !== null && r.battery_voltage !== '-' ? parseFloat(r.battery_voltage) : null),
                        borderColor: '#ffa500',
                        backgroundColor: 'rgba(255, 165, 0, 0.1)',
                        tension: 0.1,
                        spanGaps: true
                    }]
                };
            } else {
                // 數值圖表
                return {
                    labels: records.map(r => new Date(r.trigger_time)),
                    datasets: [{
                        label: sensorName + ' 數值',
                        data: records.map(r => r.value !== null && r.value !== '-' ? parseFloat(r.value) : null),
                        borderColor: '#32B8C6',
                        backgroundColor: 'rgba(50, 184, 198, 0.1)',
                        tension: 0.1,
                        spanGaps: true
                    }]
                };
            }

        case 'rain':
        case 'water':
        case 'battery':
            // 一般數值圖表
            return {
                labels: records.map(r => new Date(r.trigger_time)),
                datasets: [{
                    label: sensorName,
                    data: records.map(r => r.value !== null && r.value !== '-' ? parseFloat(r.value) : null),
                    borderColor: '#32B8C6',
                    backgroundColor: 'rgba(50, 184, 198, 0.1)',
                    tension: 0.1,
                    spanGaps: true
                }]
            };

        case 'power':
            // 市電狀態圖表（1=正常, 0=斷電）
            return {
                labels: records.map(r => new Date(r.trigger_time)),
                datasets: [{
                    label: '市電狀態 (1=正常, 0=斷電)',
                    data: records.map(r => r.value !== null && r.value !== '-' ? parseInt(r.value) : null),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    stepped: true,
                    spanGaps: true
                }]
            };

        case 'hengshen_tilt':
        case 'hengshen_load':
        case 'hengshen_strain':
            // 恆聖工程系統 - 多軸數據
            return prepareHengshenChartData(sensorType, records);

        default:
            return null;
    }
}

// 準備恆聖工程系統圖表數據
function prepareHengshenChartData(sensorType, records) {
    const colors = ['#32B8C6', '#ffa500', '#10b981', '#ef4444'];

    switch (sensorType) {
        case 'hengshen_tilt':
            return {
                labels: records.map(r => new Date(r.data_time)),
                datasets: [
                    {
                        label: '傾斜計1 X軸',
                        data: records.map(r => r.TI_1X),
                        borderColor: colors[0],
                        backgroundColor: colors[0] + '20',
                        tension: 0.1
                    },
                    {
                        label: '傾斜計1 Y軸',
                        data: records.map(r => r.TI_1Y),
                        borderColor: colors[1],
                        backgroundColor: colors[1] + '20',
                        tension: 0.1
                    },
                    {
                        label: '傾斜計2 X軸',
                        data: records.map(r => r.TI_2X),
                        borderColor: colors[2],
                        backgroundColor: colors[2] + '20',
                        tension: 0.1
                    },
                    {
                        label: '傾斜計2 Y軸',
                        data: records.map(r => r.TI_2Y),
                        borderColor: colors[3],
                        backgroundColor: colors[3] + '20',
                        tension: 0.1
                    }
                ]
            };

        case 'hengshen_load':
            return {
                labels: records.map(r => new Date(r.data_time)),
                datasets: [
                    {
                        label: '荷重計1',
                        data: records.map(r => r.LC_1),
                        borderColor: colors[0],
                        backgroundColor: colors[0] + '20',
                        tension: 0.1
                    },
                    {
                        label: '荷重計2',
                        data: records.map(r => r.LC_2),
                        borderColor: colors[1],
                        backgroundColor: colors[1] + '20',
                        tension: 0.1
                    },
                    {
                        label: '荷重計3',
                        data: records.map(r => r.LC_3),
                        borderColor: colors[2],
                        backgroundColor: colors[2] + '20',
                        tension: 0.1
                    }
                ]
            };

        case 'hengshen_strain':
            return {
                labels: records.map(r => new Date(r.data_time)),
                datasets: [
                    {
                        label: '應變計1 上',
                        data: records.map(r => r.VG_1UP),
                        borderColor: colors[0],
                        backgroundColor: colors[0] + '20',
                        tension: 0.1
                    },
                    {
                        label: '應變計1 下',
                        data: records.map(r => r.VG_1DN),
                        borderColor: colors[1],
                        backgroundColor: colors[1] + '20',
                        tension: 0.1
                    },
                    {
                        label: '應變計2 上',
                        data: records.map(r => r.VG_2UP),
                        borderColor: colors[2],
                        backgroundColor: colors[2] + '20',
                        tension: 0.1
                    },
                    {
                        label: '應變計2 下',
                        data: records.map(r => r.VG_2DN),
                        borderColor: colors[3],
                        backgroundColor: colors[3] + '20',
                        tension: 0.1
                    }
                ]
            };
    }
}