<x-guest-layout>

    <div class="password-reset-page">

        <!-- CanvasNote ロゴ -->
        <div class="password-reset-logo">
            <span class="logo-icon">📚</span>
            <span>CanvasNote</span>
        </div>


        <!-- パスワード再設定カード -->
        <div class="password-reset-card">

            <h1 class="password-reset-title">
                パスワードの再設定
            </h1>


            <p class="password-reset-description">
                登録しているメールアドレスを入力してください。
                <br>
                パスワード再設定用のメールを送信します。
            </p>


            <!-- Session Status -->
            <x-auth-session-status class="password-reset-status" :status="session('status')" />


            <form method="POST" action="{{ route('password.email') }}">
                @csrf


                <!-- メールアドレス -->
                <div class="form-group">

                    <x-input-label for="email" value="メールアドレス" class="form-label" />

                    <x-text-input id="email" class="reset-input" type="email" name="email" :value="old('email')"
                        required autofocus autocomplete="email" />

                    <x-input-error :messages="$errors->get('email')" class="form-error" />

                </div>


                <!-- 送信ボタン -->
                <button type="submit" class="reset-button">
                    再設定メールを送信
                </button>


                <!-- ログインへ戻る -->
                <div class="back-to-login">

                    <a href="{{ route('login') }}">
                        ログイン画面に戻る
                    </a>

                </div>

            </form>

        </div>

    </div>

</x-guest-layout>


<style>
    /* =========================
       ページ
    ========================== */

    .password-reset-page {
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

    .password-reset-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
        color: #222;
        font-size: 26px;
        font-weight: 700;
    }


    .logo-icon {
        font-size: 27px;
    }


    /* =========================
       カード
    ========================== */

    .password-reset-card {
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

    .password-reset-title {
        margin: 0 0 18px;
        color: #222;
        font-size: 22px;
        font-weight: 600;
        text-align: center;
    }


    /* =========================
       説明
    ========================== */

    .password-reset-description {
        margin: 0 0 26px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.8;
        text-align: center;
    }


    /* =========================
       フォーム
    ========================== */

    .form-group {
        width: 100%;
    }

    .form-label {
        display: block;
        margin-bottom: 7px;
        color: #374151;
        font-size: 14px;
        font-weight: 600;
    }


    .reset-input {
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
    }


    .reset-input:focus {
        border-color: #2563eb;
        box-shadow:
            0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .form-error {
        margin-top: 6px;
        font-size: 13px;
    }

    /* =========================
       送信ボタン
    ========================== */

    .reset-button {
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
    }

    .reset-button:hover {

        background: #1d4ed8;

    }


    /* =========================
       ログインへ戻る
    ========================== */

    .back-to-login {
        margin-top: 18px;
        text-align: center;
    }

    .back-to-login a {
        color: #64748b;
        font-size: 13px;
        text-decoration: none;
    }

    .back-to-login a:hover {
        color: #2563eb;
        text-decoration: underline;
    }


    /* =========================
       スマホ
    ========================== */

    @media (max-width: 480px) {
        .password-reset-page {
            padding: 30px 16px;
        }


        .password-reset-card {
            padding: 28px 22px;
        }


        .password-reset-logo {
            font-size: 23px;
        }
    }
</style>