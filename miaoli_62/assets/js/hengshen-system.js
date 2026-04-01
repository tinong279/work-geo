// 恆聖工程系統 JavaScript

// 閾值定義
const THRESHOLDS = {
    tilt: {
        warning: 825,  // 警戒值（秒）
        danger: 1031   // 行動值（秒）
    },
    load: {
        warningMin: 36.9,  // 警戒值下限（tone）
        warningMax: 45.1,  // 警戒值上限（tone）
        dangerMin: 32.8,   // 行動值下限（tone）
        dangerMax: 49.2    // 行動值上限（tone）
    },
    strain: {
        warning: 273,  // 警戒值（με）
        danger: 556    // 行動值（με）
    }
};

// 判斷傾斜計狀態
function getTiltStatus(value) {
    const absValue = Math.abs(value);
    if (absValue >= THRESHOLDS.tilt.danger) return 'danger';
    if (absValue >= THRESHOLDS.tilt.warning) return 'warning';
    return 'normal';
}

// 判斷荷重計狀態
function getLoadStatus(value) {
    // 超出行動值範圍 → 紅燈
    if (value < THRESHOLDS.load.dangerMin || value > THRESHOLDS.load.dangerMax) {
        return 'danger';
    }
    // 超出警戒值但在行動值範圍內 → 橘燈
    if (value < THRESHOLDS.load.warningMin || value > THRESHOLDS.load.warningMax) {
        return 'warning';
    }
    // 在警戒值範圍內 → 綠燈
    return 'normal';
}

// 判斷應變計狀態
function getStrainStatus(value) {
    const absValue = Math.abs(value);
    if (absValue >= THRESHOLDS.strain.danger) return 'danger';
    if (absValue >= THRESHOLDS.strain.warning) return 'warning';
    return 'normal';
}

// 產生狀態指示器 HTML
function getStatusIndicator(status) {
    const statusClass = `status-${status}`;
    return `<span class="status-indicator ${statusClass}"></span>`;
}

// 載入資料
async function loadHengshenData() {
    try {
        const response = await fetch('api/get_hengshen_data.php');
        const result = await response.json();

        if (!result.success) {
            console.error('載入失敗:', result.error);
            return;
        }

        // 更新傾斜計資料
        updateTiltTable(result.sensors.tilt);

        // 更新荷重計資料
        updateLoadTable(result.sensors.load);

        // 更新應變計資料
        updateStrainTable(result.sensors.strain);

        // 更新時間
        document.getElementById('update-time').textContent =
            `資料更新時間：${result.data_time}`;

    } catch (error) {
        console.error('載入資料時發生錯誤:', error);
    }
}

// 更新傾斜計表格
function updateTiltTable(data) {
    const tbody = document.getElementById('tilt-data');
    tbody.innerHTML = data.map(sensor => {
        const status = getTiltStatus(sensor.value);
        return `
            <tr>
                <td class="sensor-name">${sensor.name}</td>
                <td style="text-align: center;">
                    <span class="sensor-value">${sensor.value.toFixed(3)}</span>
                </td>
                <td style="text-align: center;">
                    ${getStatusIndicator(status)}
                </td>
            </tr>
        `;
    }).join('');
}

// 更新荷重計表格
function updateLoadTable(data) {
    const tbody = document.getElementById('load-data');
    tbody.innerHTML = data.map(sensor => {
        const status = getLoadStatus(sensor.value);
        return `
            <tr>
                <td class="sensor-name">${sensor.name}</td>
                <td style="text-align: center;">
                    <span class="sensor-value">${sensor.value.toFixed(2)}</span>
                </td>
                <td style="text-align: center;">
                    ${getStatusIndicator(status)}
                </td>
            </tr>
        `;
    }).join('');
}

// 更新應變計表格
function updateStrainTable(data) {
    const tbody = document.getElementById('strain-data');
    tbody.innerHTML = data.map(sensor => {
        const status = getStrainStatus(sensor.value);
        return `
            <tr>
                <td class="sensor-name">${sensor.name}</td>
                <td style="text-align: center;">
                    <span class="sensor-value">${sensor.value.toFixed(2)}</span>
                </td>
                <td style="text-align: center;">
                    ${getStatusIndicator(status)}
                </td>
            </tr>
        `;
    }).join('');
}

// 頁面載入時執行
document.addEventListener('DOMContentLoaded', function () {
    // 初始載入
    loadHengshenData();

    // 每 3 分鐘自動更新一次
    setInterval(loadHengshenData, 3 * 60 * 1000);
});
