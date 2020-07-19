<?php
namespace tests;
require __DIR__ . '/../vendor/autoload.php';

use Nesk\Rialto\Data\JsFunction;
use Nesk\Rialto\Exceptions\Node\FatalException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nesk\Puphpeteer\Puppeteer;

class MemberTest extends TestCase
{
    // スクリーンショット撮るときの画面サイズ
    CONST SCREEN_SIZES = [
        'width' => 1920,
        'height' => 1080
    ];

    // ログの初期構築
    CONST LOG_PASS = 'puppeteer-browser.log';

    protected $puppeteer;

    protected $logger;

    protected function setUp(): void
    {
        // Monolog を使うよう設定
        $logger = new Logger('PuPHPeteer');
        $logger->pushHandler(
            // ログのパスとログレベルを指定する
            new StreamHandler(
                self::LOG_PASS,
                Logger::ERROR
            )
        );

        $this->puppeteer = new Puppeteer([
            'log_browser_console' => true,
            'logger' => $logger
        ]);
    }

    /**
     * 新規登録のテスト
     */
    public function testSignUp(): void
    {
        $browser = $this->puppeteer
            ->launch();
        $page = $browser->newPage();
        $page->setViewport(self::SCREEN_SIZES);

        try {
            // TOPページから新規登録画面に遷移する
            $page->goto(
                'https://hotel.testplanisphere.dev/'
            );
            $page->waitFor(1000);
            $signUpButtonElement = 'a[href="./signup.html"]';
            $page->click($signUpButtonElement);

            // フォームに入力後、新規登録をする
            $page->waitForSelector("#email");
            $page->waitFor(1000);
            $page->type('#email', "test@foo.jp");
            $page->waitFor(1000);
            $page->type('#password', "Testtest1");
            $page->waitFor(1000);
            $page->type(
                '#password-confirmation',
                "Testtest1"
            );
            $page->waitFor(1000);
            $page->type('#username', "山田太郎");
            $page->waitFor(1000);
            $page->click('button[type="submit"]');
            $page->waitFor("#icon-link");
            $page->waitFor(1000);

            // 新規登録できているか確認する
            $iconLinkButtonElement =
                $page->querySelector("#icon-link");
            $iconLinkText =
                $iconLinkButtonElement
                    ->getProperty('textContent')
                    ->jsonValue();
            $this->assertSame(
                'アイコン設定',
                $iconLinkText,
                '新規登録ができませんでした。'  . __LINE__ . '行目'
            );
        } catch (AssertionFailedError $e) {
            // phpunit の assertSame で
            // テストが失敗した時
            $date = new \DateTime();
            $screenshotFileName =
                $this->getName() . '-'
                . $date->format('YmdHis')
                . '.png';
            $page->screenshot([
                'path' =>
                    __DIR__
                    . '/../screenshots/'
                    . $screenshotFileName,
                'fullPage' => true
            ]);

            $this->fail(
                "\033[41m"
                . $this->getName()
                . "のテストに失敗しました\033[0m"
                . "\n"
                . '失敗時の画面スクリーンショット：'
                . $screenshotFileName . "\n"
                . 'エラーメッセージ：'
                . $e->getMessage()
                . "\n"
                . '比較：'
                . "\n"
                . $e->getComparisonFailure()
                    ->toString()
            );
        } catch (FatalException $e) {
            // phpunit の assertSame 以外で
            // テストが失敗した時
            $TraceString = '';
            foreach (
                $e->getTrace() as $trace
            ) {
                $TraceString .= $trace['file']
                    . ' '
                    . $trace['line']
                    . "行目\n";
            }

            $this->fail(
                "\033[41m"
                . $this->getName()
                . "のテストに失敗しました\033[0m"
                . "\n"
                . 'エラーメッセージ：'
                . $e->getMessage()
                . "\n"
                . 'エラートレース：'
                . "\n"
                . $TraceString
                . "\n"
            );
        }
    }

    /**
     * ログインのテスト
     */
    public function testFromSignUpToLogin(): void
    {
        $browser = $this->puppeteer
            ->launch();
        $page = $browser->newPage();
        $page->setViewport(self::SCREEN_SIZES);

        try {
            // TOPページから新規登録画面に遷移する
            $page->goto(
                'https://hotel.testplanisphere.dev/'
            );
            $page->waitFor(1000);
            $signUpButtonElement = 'a[href="./signup.html"]';
            $page->click($signUpButtonElement);

            // フォームに入力後、新規登録をする
            $page->waitForSelector("#email");
            $page->waitFor(1000);
            $page->type('#email', "test@foo.jp");
            $page->waitFor(1000);
            $page->type('#password', "Testtest1");
            $page->waitFor(1000);
            $page->type(
                '#password-confirmation',
                "Testtest1"
            );
            $page->waitFor(1000);
            $page->type('#username', "山田太郎");
            $page->waitFor(1000);
            $page->click('button[type="submit"]');
            $page->waitFor("#icon-link");
            $page->waitFor(1000);

            // 新規登録後、ログアウトをする
            $page->click(
                '#logout-form'
                . ' > button[type="submit"]'
            );

            // TOPページからログインボタンをクリック
            $page->goto(
                'https://hotel.testplanisphere.dev/'
            );
            $signInButtonElement =
                'a[href="./login.html"]';
            $page->waitForSelector(
                $signInButtonElement
            );
            $page->waitFor(1000);
            $page->click($signInButtonElement);

            // ログイン施行する
            $page->waitForSelector("#email");
            $page->waitFor(1000);
            $page->type('#email', "test@foo.jp");
            $page->waitFor(1000);
            $page->type(
                '#password',
                "Testtest1"
            );
            $page->waitFor(1000);
            $page->click('button[type="submit"]');
            $page->waitForSelector("#icon-link");
            $page->waitFor(1000);

            // ログインできているか確認する
            $iconLinkButtonElement =
                $page->querySelector(
                    "#icon-link"
                );
            $iconLinkText = $iconLinkButtonElement
                ->getProperty('textContent')
                ->jsonValue();
            $this->assertSame(
                'アイコン設定',
                $iconLinkText,
                '新規登録したアカウントで'
                . 'ログインができませんでした。'
                . __LINE__ . '行目'
            );
        } catch (AssertionFailedError $e) {
            // phpunit の assertSame で
            // テストが失敗した時
            $date = new DateTime();
            $screenshotFileName =
                $this->getName() . '-'
                . $date->format('YmdHis')
                . '.png';
            $page->screenshot([
                'path' =>
                    __DIR__
                    . '/../screenshots/'
                    . $screenshotFileName,
                'fullPage' => true
            ]);

            $this->fail(
                "\033[41m"
                . $this->getName()
                . "のテストに失敗しました\033[0m"
                . "\n"
                . '失敗時の画面スクリーンショット：'
                . $screenshotFileName . "\n"
                . 'エラーメッセージ：'
                . $e->getMessage()
                . "\n"
                . '比較：'
                . "\n"
                . $e->getComparisonFailure()
                    ->toString()
            );
        } catch (FatalException $e) {
            // phpunit の assertSame 以外で
            // テストが失敗した時
            $TraceString = '';
            foreach (
                $e->getTrace() as $trace
            ) {
                $TraceString .= $trace['file']
                    . ' '
                    . $trace['line']
                    . "行目\n";
            }

            $this->fail(
                "\033[41m"
                . $this->getName()
                . "のテストに失敗しました\033[0m"
                . "\n"
                . 'エラーメッセージ：'
                . $e->getMessage()
                . "\n"
                . 'エラートレース：'
                . "\n"
                . $TraceString
                . "\n"
            );
        }
    }

    /**
     * 退会のテスト
     */
    public function testFromSignUpToQuit(): void
    {
        $browser = $this->puppeteer
            ->launch();
        $page = $browser->newPage();
        $page->setViewport(self::SCREEN_SIZES);

        try {
            // TOPページから新規登録画面に遷移する
            $page->goto(
                'https://hotel.testplanisphere.dev/'
            );
            $page->waitFor(1000);
            $signUpButtonElement = 'a[href="./signup.html"]';
            $page->click($signUpButtonElement);

            // フォームに入力後、新規登録をする
            $page->waitForSelector("#email");
            $page->waitFor(1000);
            $page->type('#email', "test@foo.jp");
            $page->waitFor(1000);
            $page->type('#password', "Testtest1");
            $page->waitFor(1000);
            $page->type(
                '#password-confirmation',
                "Testtest1"
            );
            $page->waitFor(1000);
            $page->type('#username', "山田太郎");
            $page->waitFor(1000);
            $page->click('button[type="submit"]');
            $page->waitFor("#icon-link");
            $page->waitFor(1000);

            // 新規登録後、ログアウトをする
            $page->click(
                '#logout-form'
                . ' > button[type="submit"]'
            );

            // TOPページからログインボタンをクリック
            $page->goto(
                'https://hotel.testplanisphere.dev/'
            );
            $signInButtonElement =
                'a[href="./login.html"]';
            $page->waitForSelector(
                $signInButtonElement
            );
            $page->waitFor(1000);
            $page->click($signInButtonElement);

            // ログイン施行する
            $page->waitForSelector("#email");
            $page->waitFor(1000);
            $page->type('#email', "test@foo.jp");
            $page->waitFor(1000);
            $page->type(
                '#password',
                "Testtest1"
            );
            $page->waitFor(1000);
            $page->click('button[type="submit"]');
            $page->waitForSelector("#icon-link");
            $page->waitFor(1000);

            // TOPページからマイページへ遷移する
            $page->goto(
                'https://hotel.testplanisphere.dev/'
            );
            $mypageButtonElement = 'a[href="./mypage.html"]';
            $page->waitForSelector(
                $mypageButtonElement
            );
            $page->waitFor(1000);
            $page->click($mypageButtonElement);

            // 退会処理する
            $confirmFunction =
                JsFunction::createWithAsync()
                    ->parameters(['dialog'])
                    ->body('await dialog.accept();');
            $page->on(
                'dialog',
                $confirmFunction
            );
            $deleteButtonElement
                = '#delete-form > button[type="submit"]';
            $page->waitForSelector($deleteButtonElement);
            $page->waitFor(1000);
            $page->click($deleteButtonElement);

            // もう一回ログインして、ログインできないことを確認する
            $signInButtonElement = 'a[href="./login.html"]';
            $page->waitForSelector($signInButtonElement);
            $page->waitFor(1000);
            $page->click($signInButtonElement);
            $page->waitForSelector("#email");
            $page->waitFor(1000);
            $page->type('#email', "test@foo.jp");
            $page->waitFor(1000);
            $page->type('#password', "Testtest1");
            $page->waitFor(1000);
            $page->click('button[type="submit"]');
            $page->waitFor(1000);

            $errorMessageElement =
                $page->querySelector("#password-message");
            $errorMessageText =
                $errorMessageElement
                    ->getProperty('textContent')
                    ->jsonValue();
            $this->assertSame(
                'メールアドレスまたは'
                . 'パスワードが違います。',
                $errorMessageText,
                '退会ができませんでした。'
                . __LINE__ . '行目'
            );
        } catch (AssertionFailedError $e) {
            // phpunit の assertSame で
            // テストが失敗した時
            $date = new DateTime();
            $screenshotFileName =
                $this->getName() . '-'
                . $date->format('YmdHis')
                . '.png';
            $page->screenshot([
                'path' =>
                    __DIR__
                    . '/../screenshots/'
                    . $screenshotFileName,
                'fullPage' => true
            ]);

            $this->fail(
                "\033[41m"
                . $this->getName()
                . "のテストに失敗しました\033[0m"
                . "\n"
                . '失敗時の画面スクリーンショット：'
                . $screenshotFileName . "\n"
                . 'エラーメッセージ：'
                . $e->getMessage()
                . "\n"
                . '比較：'
                . "\n"
                . $e->getComparisonFailure()
                    ->toString()
            );
        } catch (FatalException $e) {
            // phpunit の assertSame 以外で
            // テストが失敗した時
            $TraceString = '';
            foreach (
                $e->getTrace() as $trace
            ) {
                $TraceString .= $trace['file']
                    . ' '
                    . $trace['line']
                    . "行目\n";
            }

            $this->fail(
                "\033[41m"
                . $this->getName()
                . "のテストに失敗しました\033[0m"
                . "\n"
                . 'エラーメッセージ：'
                . $e->getMessage()
                . "\n"
                . 'エラートレース：'
                . "\n"
                . $TraceString
                . "\n"
            );
        }

        $browser->close();
    }
}