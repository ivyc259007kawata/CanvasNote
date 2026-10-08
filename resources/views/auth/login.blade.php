<x-guest-layout>

    <div class="login-page">

        <!-- CanvasNote ロゴ -->
        <div class="login-logo">
            <span class="logo-icon">📚</span>
            <span>CanvasNote</span>
        </div>

        <!-- ログインカード -->
        <div class="login-card">

            <h1 class="login-title">
                ログイン
            </h1>

            <x-auth-session-status class="login-status" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- ログインID / メールアドレス -->
                <div class="form-group">

                    <x-input-label for="login" value="ログインID / メールアドレス" class="form-label" />

                    <x-text-input id="login" class="login-input" type="text" name="login" :value="old('login')" required
                        autofocus autocomplete="username" />

                    <x-input-error :messages="$errors->get('login')" class="form-error" />

                </div>


                <!-- パスワード -->
                <div class="form-group password-group">

                    <x-input-label for="password" value="パスワード" class="form-label" />

                    <x-text-input id="password" class="login-input" type="password" name="password" required
                        autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="form-error" />

                </div>


                <!-- ログイン状態を保持 -->
                <div class="remember-area">

                    <label for="remember_me" class="remember-label">

                        <input id="remember_me" type="checkbox" name="remember" class="remember-checkbox">

                        <span>
                            ログイン状態を保持する
                        </span>

                    </label>

                </div>


                <!-- パスワードを忘れた場合 -->
                @if (Route::has('password.request'))
                    <div class="forgot-password">
                        <a href="{{ route('password.request') }}">
                            パスワードを忘れた場合
                        </a>
                    </div>
                @endif


                <!-- ログインボタン -->
                <button type="submit" class="login-button">
                    ログイン
                </button>

            </form>
        </div>
    </div>

</x-guest-layout>


<style>
    /* =========================
       ログイン画面
    ========================== */

    .login-page {
        width: 100%;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        padding: 40px 20px;
        background: #f8fafc;
    }

    /* =========================
       ロゴ
    ========================== */

    .login-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
        color: #222;
        font-size: 26px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }


    .logo-icon {
        font-size: 27px;
    }

    /* =========================
       ログインカード
    ========================== */

    .login-card {
        width: 100%;
        max-width: 420px;
        padding: 34px 36px;
        box-sizing: border-box;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow:
            0 4px 12px rgba(15, 23, 42, 0.05);
    }

    /* =========================
       タイトル
    ========================== */

    .login-title {
        margin: 0 0 28px;
        color: #222;
        font-size: 22px;
        font-weight: 600;
        text-align: center;
    }

    /* =========================
       フォーム
    ========================== */

    .form-group {
        width: 100%;
    }

    .password-group {
        margin-top: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 7px;
        color: #374151;
        font-size: 14px;
        font-weight: 600;
    }


    .login-input {
        width: 100%;
        min-height: 42px;
        padding: 9px 12px;
        box-sizing: border-box;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: white;
        color: #333;
        font-size: 14px;
        outline: none;
        transition:
            border-color 0.2s,
            box-shadow 0.2s;
    }


    .login-input:focus {
        border-color: #2563eb;
        box-shadow:
            0 0 0 3px rgba(37, 99, 235, 0.12);
    }


    .form-error {
        margin-top: 6px;
        font-size: 13px;
    }

    /* =========================
       ログイン状態
    ========================== */

    .remember-area {
        margin-top: 18px;
    }

    .remember-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 13px;
        cursor: pointer;
    }

    .remember-checkbox {
        width: 15px;
        height: 15px;
        accent-color: #2563eb;
        cursor: pointer;
    }

    /* =========================
    パスワード忘れ
    ========================== */

    .forgot-password {
        margin-top: 18px;
        text-align: center;
    }


    .forgot-password a {
        color: #64748b;
        font-size: 13px;
        text-decoration: none;
    }

    .forgot-password a:hover {
        color: #2563eb;
        text-decoration: underline;
    }

    /* =========================
    ログインボタン
    ========================== */

    .login-button {
        width: 100%;
        margin-top: 22px;
        padding: 11px 16px;
        border: none;
        border-radius: 8px;
        background: #2563eb;
        color: white;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition:
            background 0.2s,
            transform 0.1s;
    }

    .login-button:hover {
        background: #1d4ed8;
    }

    .login-button:active {
        transform: translateY(1px);
    }

    /* =========================
       スマホ
    ========================== */

    @media (max-width: 480px) {

        .login-page {
            padding: 30px 16px;
        }

        .login-card {
            padding: 28px 22px;
        }

        .login-logo {
            font-size: 23px;
        }
    }
</style>