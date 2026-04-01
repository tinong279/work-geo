/* =========================================
   board-controller.js
   功能：
   1. 管理牌面模式
   2. 一般模式：依車速決定文字
   3. 事故模式：人工覆蓋文字
   ========================================= */

/* =========================================
   1. 牌面狀態
   - normal   = 一般模式
   - incident = 事故模式
   ========================================= */
const BoardController = {
  state: {
    mode: "normal",
    incidentMessage: "",
  },

  /* =========================================
       2. 切換成一般模式
       ========================================= */
  setNormalMode() {
    this.state.mode = "normal";
    this.state.incidentMessage = "";
  },

  /* =========================================
       3. 切換成事故模式
       message 範例：
       - 前方有車禍
       - 請小心駕駛
       ========================================= */
  setIncidentMode(message) {
    this.state.mode = "incident";
    this.state.incidentMessage = message || "請小心駕駛";
  },

  /* =========================================
       4. 依車速判斷一般模式的牌面內容
       規則：
       - speed < 30      => 前方道路壅塞
       - 30 <= speed <70 => 車多
       - speed >= 70     => 暢通
       ========================================= */
  getNormalBoardBySpeed(speed) {
    speed = Number(speed || 0);

    if (speed < 30) {
      return {
        message: "前方道路壅塞",
        shortStatus: "壅塞",
        textClass: "text-red",
        lineColor: "#ff0000",
      };
    }

    if (speed < 70) {
      return {
        message: "車多",
        shortStatus: "車多",
        textClass: "text-yellow",
        lineColor: "#ffff00",
      };
    }

    return {
      message: "暢通",
      shortStatus: "暢通",
      textClass: "text-green",
      lineColor: "#00ff00",
    };
  },

  /* =========================================
       5. 根據目前模式，決定牌面顯示內容
       - 一般模式：依速度
       - 事故模式：顯示人工訊息
       ========================================= */
  getBoardDisplayInfo(speed) {
    if (this.state.mode === "incident") {
      return {
        message: this.state.incidentMessage || "請小心駕駛",
        shortStatus: "事故",
        textClass: "text-orange",
        lineColor: "#ff9800",
      };
    }

    return this.getNormalBoardBySpeed(speed);
  },

  /* =========================================
       6. 取得目前模式名稱
       方便前端顯示 badge
       ========================================= */
  getModeLabel() {
    if (this.state.mode === "incident") {
      return `事故模式（${this.state.incidentMessage}）`;
    }
    return "一般模式";
  },
};
