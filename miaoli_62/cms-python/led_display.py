"""
CP5200 LED 看板控制模組
支援文字顯示、顏色設定、特效等功能
"""

import ctypes
import socket
import os
import sys


class LEDDisplay:
    """CP5200 LED 看板控制器"""

    # 特效常數
    EFFECT_NONE = 0                    # 無特效
    EFFECT_SCROLL_LEFT = 1             # 從左至右跑馬燈
    EFFECT_SCROLL_RIGHT = 2            # 從右至左跑馬燈
    EFFECT_SCROLL_RIGHT_SLOW = 3       # 從右至左跑馬燈(慢速逐格)
    EFFECT_SCROLL_UP = 4               # 從下至上跑馬燈(慢速逐格)
    EFFECT_BLINK = 13                  # 閃爍

    # 對齊方式
    ALIGN_LEFT = 0                     # 靠左
    ALIGN_CENTER = 1                   # 置中
    ALIGN_RIGHT = 2                    # 靠右

    # 顏色代碼 (BGR 格式)
    COLOR_RED = 0x0000FF
    COLOR_GREEN = 0x00FF00
    COLOR_BLUE = 0xFF0000
    COLOR_YELLOW = 0x00FFFF
    COLOR_WHITE = 0xFFFFFF

    def __init__(self, ip="192.168.1.222", port=5200,
                 width=32, height=16,
                 card_id=1, id_code=0xFFFFFFFF, timeout=3000,
                 dll_path=None):
        """
        初始化 LED 看板控制器

        Args:
            ip: LED 看板 IP 位址
            port: 連接埠號
            width: 看板寬度
            height: 看板高度
            card_id: 卡號
            id_code: 識別碼
            timeout: 連線逾時(毫秒)
            dll_path: CP5200.dll 路徑，None 則自動尋找
        """
        self.ip = ip
        self.port = port
        self.width = width
        self.height = height
        self.card_id = card_id
        self.id_code = id_code
        self.timeout = timeout

        # 載入 DLL
        if dll_path is None:
            script_dir = os.path.dirname(os.path.abspath(__file__))
            dll_path = os.path.join(script_dir, "CP5200.dll")

        if not os.path.exists(dll_path):
            raise FileNotFoundError(f"找不到 DLL: {dll_path}")

        # Python 3.8+ Windows 需要額外設定
        if sys.platform == "win32" and sys.version_info >= (3, 8):
            dll_dir = os.path.dirname(dll_path)
            if dll_dir:
                os.add_dll_directory(dll_dir)

        self.dll = ctypes.WinDLL(dll_path)
        self._bind_functions()
        self._initialized = False

    def _bind_functions(self):
        """綁定 DLL 函式"""
        def bind(name, argtypes, restype):
            fn = getattr(self.dll, name, None)
            if not fn:
                raise RuntimeError(f"找不到函式: {name}")
            fn.argtypes = argtypes
            fn.restype = restype
            return fn

        self.Net_Init = bind("CP5200_Net_Init",
                             [ctypes.c_uint32, ctypes.c_int,
                                 ctypes.c_uint32, ctypes.c_int],
                             ctypes.c_int)

        self.Net_SplitScreen = bind("CP5200_Net_SplitScreen",
                                    [ctypes.c_int, ctypes.c_int, ctypes.c_int, ctypes.c_int,
                                     ctypes.POINTER(ctypes.c_int)],
                                    ctypes.c_int)

        self.Net_SendTagText = bind("CP5200_Net_SendTagText",
                                    [ctypes.c_int, ctypes.c_int, ctypes.c_char_p,
                                     ctypes.c_ulong, ctypes.c_int, ctypes.c_int,
                                     ctypes.c_int, ctypes.c_int, ctypes.c_int],
                                    ctypes.c_int)

        # 可選的斷線函式
        try:
            self.Net_Disconnect = bind(
                "CP5200_Net_Disconnect", [], ctypes.c_int)
            self._has_disconnect = True
        except:
            self._has_disconnect = False

    def connect(self):
        """連接到 LED 看板"""
        ip_dword = ctypes.c_uint32(int.from_bytes(
            socket.inet_aton(self.ip), "big"))

        ret = self.Net_Init(ip_dword, self.port,
                            ctypes.c_uint32(self.id_code), self.timeout)

        if ret != 0:
            raise RuntimeError(f"網路初始化失敗，錯誤碼: {ret}")

        # 設定全螢幕視窗
        rect = (ctypes.c_int * 4)(0, 0, self.width, self.height)
        ret = self.Net_SplitScreen(
            self.card_id, self.width, self.height, 1, rect)

        if ret != 0:
            print(f"警告：SplitScreen 回傳 {ret}")

        self._initialized = True
        return True

    def disconnect(self):
        """斷開連接"""
        if self._has_disconnect and self._initialized:
            ret = self.Net_Disconnect()
            self._initialized = False
            return ret == 0
        return True

    def show_text(self, text, color=None, font_size=16,
                  speed=3, effect=0, stay=3, align=0):
        """
        顯示文字到 LED 看板

        Args:
            text: 要顯示的文字
            color: 顏色 (BGR 格式)，可用 COLOR_RED 等常數
            font_size: 字體大小 (8-16)
            speed: 速度 (0-7)
            effect: 特效 (使用 EFFECT_* 常數)
            stay: 停留時間
            align: 對齊方式 (使用 ALIGN_* 常數)

        Returns:
            bool: 成功返回 True
        """
        if not self._initialized:
            self.connect()

        # 預設紅色
        if color is None:
            color = self.COLOR_RED

        # 轉換為 Big5 編碼 (繁體中文)
        text_bytes = text.encode('big5', errors='ignore')

        ret = self.Net_SendTagText(
            self.card_id, 0, text_bytes, color,
            font_size, speed, effect, stay, align
        )

        if ret != 0:
            raise RuntimeError(f"發送文字失敗，錯誤碼: {ret}")

        return True

    def __enter__(self):
        """支援 with 語法"""
        self.connect()
        return self

    def __exit__(self, exc_type, exc_val, exc_tb):
        """自動斷開連接"""
        self.disconnect()


def show_message(text, ip="192.168.1.222", port=5200,
                 color=LEDDisplay.COLOR_RED, effect=LEDDisplay.EFFECT_NONE):
    """
    快速顯示訊息（自動連接與斷開）

    Args:
        text: 要顯示的文字
        ip: LED 看板 IP
        port: 連接埠
        color: 顏色
        effect: 特效

    Example:
        show_message("測試訊息")
        show_message("跑馬燈", effect=LEDDisplay.EFFECT_SCROLL_RIGHT)
    """
    with LEDDisplay(ip=ip, port=port) as led:
        led.show_text(text, color=color, effect=effect)
        return True


if __name__ == "__main__":

    show_message("測試訊息")

    print("\n完成！")
