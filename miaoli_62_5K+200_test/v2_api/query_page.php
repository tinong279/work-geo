<div class="content-header">
    <h2 class="content-title">時間區間查詢</h2>
</div>

<div class="monitor-card" style="background: #fff; border: 1px solid #d2d6de; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 4px; margin-bottom: 20px;">
    <div class="card-content" style="padding: 20px;">
        <form id="history-form" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 14px; color: #666; margin-bottom: 8px;">查詢類別</label>
                <select id="q-device" style="width: 90%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px;">
                    <option value="utilityPower">市電歷史資料</option>
                    <option value="battery_voltage">電池電壓歷史資料</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; font-size: 14px; color: #666; margin-bottom: 8px;">開始時間</label>
                <input type="text" id="q-start" class="datetimepicker" value="2025-01-01 00:00" style="width: 90%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px;">
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; font-size: 14px; color: #666; margin-bottom: 8px;">結束時間</label>
                <input type="text" id="q-end" class="datetimepicker" value="<?php echo date('Y-m-d H:i'); ?>" style="width: 90%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px;">
            </div>
            <button type="button" onclick="executeSearch()" style="background-color: #007bff; color: white; border: none; padding: 10px 30px; border-radius: 25px; cursor: pointer; font-size: 16px; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                🔍 查詢
            </button>
        </form>
    </div>
</div>

<div class="monitor-card" style="border: 1px solid #d2d6de; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
    <div class="card-header" style="background-color: #f7f7f7; padding: 12px 20px; border-bottom: 1px solid #f4f4f4; font-weight: bold;">
        📊 圖表 詳細資料
    </div>
    <div class="card-body" style="padding: 20px; min-height: 400px;">
        <div id="chart-status" style="text-align: center; color: #999; margin-top: 50px;">請選擇時間區間並點擊查詢</div>
        <canvas id="historyChart" style="display: none; width: 100%; height: 350px;"></canvas>
    </div>
</div>