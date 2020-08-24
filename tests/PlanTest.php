<?php
namespace tests;
require __DIR__ . '/../vendor/autoload.php';

use Nesk\Rialto\Exceptions\Node\FatalException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nesk\Puphpeteer\Puppeteer;

class PlanTest extends TestCase
{
    // スクリーンショット撮るときの画面サイズ
    CONST SCREEN_SIZES = [
        'width' => 1920, 'height' => 1080
    ];

    // ログのパスと名前
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

        $this->puppeteer = new Puppeteer(
            [
                'log_browser_console' => true,
                'logger' => $logger
            ]
        );
    }

    /**
     * 宿泊プラン一覧画面の確認テスト
     */
    public function testFromSignUpToPlanIndex(): void
    {
        $browser = $this->puppeteer
            ->launch();
        $page = $browser->newPage();
        $page->setViewport(self::SCREEN_SIZES);

        try {
            // TOPページにアクセスしてからログイン画面へ遷移
            $page->goto(
                'https://hotel.testplanisphere.dev/ja/'
            );
            $signInButtonElement = 'a[href="./login.html"]';
            $page->waitForSelector($signInButtonElement);
            $page->click($signInButtonElement);

            // プレミア会員でログインする
            $page->waitForSelector("#email");
            $page->waitFor(1000);
            $page->type('#email', "ichiro@example.com");
            $page->waitFor(1000);
            $page->type('#password', "password");
            $page->waitFor(1000);
            $page->click('button[type="submit"]');
            $page->waitFor(1000);

            // TOPページから宿泊プラン一覧画面へ遷移する
            $page->goto(
                'https://hotel.testplanisphere.dev/ja/'
            );
            $planButtonElement = 'a[href="./plans.html"]';
            $page->waitForSelector($planButtonElement);
            $page->waitFor(1000);
            $page->click($planButtonElement);

            // プレミアム会員限定のプレミアムプランが
            // リストに存在することを確認する
            $planListElement = '#plan-list';
            $page->waitForSelector($planListElement);
            $page->waitFor(1000);
            $premiumPlanElement = $planListElement
                . ' > div.col-12.col-md-6.col-lg-4'
                . ' > div.card.text-center.shadow-sm.mb-3'
                . ' > div.card-header';
            $planTextElements =
                $page->querySelectorAll($premiumPlanElement);
            $premiumPlanText = '';
            foreach ($planTextElements as $planTextElement) {
                $planText =
                    $planTextElement
                        ->getProperty('textContent')
                        ->jsonValue();
                if ($planText === '❤️プレミアム会員限定❤️') {
                    $premiumPlanText = $planText;
                }
            }
            $this->assertSame(
                '❤️プレミアム会員限定❤️',
                $premiumPlanText,
                'プレミアム会員限定プランを選択できませんでした。'
                . '該当行数：' . __LINE__ . '行目'
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
            foreach ($e->getTrace() as $trace) {
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

    /**
     * 宿泊プランと金額の確認テスト
     */
    public function testFromSignInToEntryReservation(): void
    {
        $browser = $this->puppeteer
            ->launch();
        $page = $browser->newPage();
        $page->setViewport(self::SCREEN_SIZES);

        try {
            // TOPページにアクセスしてからログイン画面へ遷移
            $page->goto(
                'https://hotel.testplanisphere.dev/ja/'
            );
            $signInButtonElement = 'a[href="./login.html"]';
            $page->waitForSelector($signInButtonElement);
            $page->click($signInButtonElement);

            // プレミア会員でログインする
            $page->waitForSelector("#email");
            $page->waitFor(1000);
            $page->type('#email', "ichiro@example.com");
            $page->waitFor(1000);
            $page->type('#password', "password");
            $page->waitFor(1000);
            $page->click('button[type="submit"]');
            $page->waitFor(1000);

            // TOPページから宿泊プラン一覧画面へ遷移する
            $page->goto(
                'https://hotel.testplanisphere.dev/ja/'
            );
            $planButtonElement = 'a[href="./plans.html"]';
            $page->waitForSelector($planButtonElement);
            $page->waitFor(1000);
            $page->click($planButtonElement);

            // プレミアムプランを選択して、宿泊予約入力画面に遷移する
            $premiumPlanButtonElement =
                'a[href="./reserve.html?plan-id=1"]';
            $page->waitFor($premiumPlanButtonElement);
            $page->click($premiumPlanButtonElement);
            $page->waitFor(2000);
            $pages = $browser->pages();
            $page = $pages[2];
            $page->setViewport(self::SCREEN_SIZES);

            // フォームを入力して算出金額が正しいか確認する
            $page->waitForSelector("#date");
            $page->waitFor(1000);
            $date = $page->querySelector('#date');
            $date->click(["clickCount" => 3]);
            $page->waitFor(1000);
            $nowDate = new \DateTime();
            $nextMonthDate = $nowDate->modify('+1 month');
            $page->type("#date", $nextMonthDate);
            $page->waitFor(1000);
            $planDesc = $page->querySelector('#plan-desc');
            $planDesc->click();
            $page->waitFor(1000);
            $term = $page->querySelector('#term');
            $term->click(["clickCount" => 3]);
            $page->waitFor(1000);
            $page->type("#term", "3");
            $page->waitFor(1000);
            $headCount = $page->querySelector('#head-count');
            $headCount->click(["clickCount" => 3]);
            $page->waitFor(1000);
            $page->type("#head-count", "4");
            $page->waitFor(1000);
            $page->click("#breakfast");
            $page->waitFor(1000);
            $page->click("#early-check-in");
            $page->waitFor(1000);
            $page->click("#sightseeing");
            $page->waitFor(1000);
            $page->select('#contact', 'email');
            $page->waitFor(1000);
            $billText =
                $page
                    ->querySelector('#total-bill')
                    ->getProperty('textContent')
                    ->jsonValue();
            $this->assertSame("150,000円", $billText);
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
            foreach ($e->getTrace() as $trace) {
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

    /**
     * 宿泊予約入力のテスト
     */
    public function testFromSignInToReserveConfirm(): void
    {
        $browser = $this->puppeteer
            ->launch();
        $page = $browser->newPage();
        $page->setViewport(self::SCREEN_SIZES);

        try {
            // TOPページにアクセスしてからログイン画面へ遷移
            $page->goto(
                'https://hotel.testplanisphere.dev/ja/'
            );
            $signInButtonElement = 'a[href="./login.html"]';
            $page->waitForSelector($signInButtonElement);
            $page->click($signInButtonElement);

            // プレミア会員でログインする
            $page->waitForSelector("#email");
            $page->waitFor(1000);
            $page->type('#email', "ichiro@example.com");
            $page->waitFor(1000);
            $page->type('#password', "password");
            $page->waitFor(1000);
            $page->click('button[type="submit"]');
            $page->waitFor(1000);

            // TOPページから宿泊プラン一覧画面へ遷移する
            $page->goto(
                'https://hotel.testplanisphere.dev/ja/'
            );
            $planButtonElement = 'a[href="./plans.html"]';
            $page->waitForSelector($planButtonElement);
            $page->waitFor(1000);
            $page->click($planButtonElement);

            // プレミアムプランを選択して、宿泊予約入力画面に遷移する
            $premiumPlanButtonElement =
                'a[href="./reserve.html?plan-id=1"]';
            $page->waitFor($premiumPlanButtonElement);
            $page->click($premiumPlanButtonElement);
            $page->waitFor(2000);
            $pages = $browser->pages();
            $page = $pages[2];
            $page->setViewport(self::SCREEN_SIZES);

            // フォームを入力して宿泊予約入力画面に遷移する
            $page->waitForSelector("#date");
            $page->waitFor(1000);
            $date = $page->querySelector('#date');
            $date->click(["clickCount" => 3]);
            $page->waitFor(1000);
            $page->type("#date", "2020/08/06");
            $page->waitFor(1000);
            $planDesc = $page->querySelector('#plan-desc');
            $planDesc->click();
            $page->waitFor(1000);
            $term = $page->querySelector('#term');
            $term->click(["clickCount" => 3]);
            $page->waitFor(1000);
            $page->type("#term", "3");
            $page->waitFor(1000);
            $headCount = $page->querySelector('#head-count');
            $headCount->click(["clickCount" => 3]);
            $page->waitFor(1000);
            $page->type("#head-count", "4");
            $page->waitFor(1000);
            $page->click("#breakfast");
            $page->waitFor(1000);
            $page->click("#early-check-in");
            $page->waitFor(1000);
            $page->click("#sightseeing");
            $page->waitFor(1000);
            $page->select('#contact', 'email');
            $page->waitFor(1000);
            $page->click("#submit-button");
            $page->waitForSelector('#term');
            $page->waitFor(1000);

            // 予約内容が確認画面に
            // 正しく表示されているか確認する
            $termText =
                $page->querySelector('#term')
                    ->getProperty('textContent')
                    ->jsonValue();
            $headCountText =
                $page->querySelector('#head-count')
                    ->getProperty('textContent')
                    ->jsonValue();
            $plansText =
                $page->querySelector('#plans')
                    ->getProperty('textContent')
                    ->jsonValue();
            $usernameText =
                $page->querySelector('#username')
                    ->getProperty('textContent')
                    ->jsonValue();
            $contactText =
                $page->querySelector('#contact')
                    ->getProperty('textContent')
                    ->jsonValue();
            $this->assertSame(
                "2020年8月6日 〜 2020年8月9日 3泊",
                $termText,
                '宿泊期間の表示に誤りがあります。'
                . '該当行数：' . __LINE__ . '行目'
            );
            $this->assertSame(
                "4名様",
                $headCountText,
                '宿泊人数の表示に誤りがあります。'
                . '該当行数：' . __LINE__ . '行目'
            );
            $this->assertSame(
                "朝食バイキング昼からチェックインプラン"
                . "お得な観光プラン",
                $plansText,
                'プランの表示に誤りがあります。'
                . '該当行数：' . __LINE__ . '行目'
            );
            $this->assertSame(
                "山田一郎様",
                $usernameText,
                '宿泊予約者の表示に誤りがあります。'
                . '該当行数：' . __LINE__ . '行目'
            );
            $this->assertSame(
                "メール：ichiro@example.com",
                $contactText,
                '宿泊予約者のメール表示に誤りがあります。'
                . '該当行数：' . __LINE__ . '行目'
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
            foreach ($e->getTrace() as $trace) {
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

    /**
     * 宿泊予約入力のテスト
     */
    public function testFromSignInToReserve(): void
    {
        $browser = $this->puppeteer
            ->launch();
        $page = $browser->newPage();
        $page->setViewport(self::SCREEN_SIZES);

        try {
            // TOPページにアクセスしてからログイン画面へ遷移
            $page->goto(
                'https://hotel.testplanisphere.dev/ja/'
            );
            $signInButtonElement = 'a[href="./login.html"]';
            $page->waitForSelector($signInButtonElement);
            $page->click($signInButtonElement);

            // プレミア会員でログインする
            $page->waitForSelector("#email");
            $page->waitFor(1000);
            $page->type('#email', "ichiro@example.com");
            $page->waitFor(1000);
            $page->type('#password', "password");
            $page->waitFor(1000);
            $page->click('button[type="submit"]');
            $page->waitFor(1000);

            // TOPページから宿泊プラン一覧画面へ遷移する
            $page->goto(
                'https://hotel.testplanisphere.dev/ja/'
            );
            $planButtonElement = 'a[href="./plans.html"]';
            $page->waitForSelector($planButtonElement);
            $page->waitFor(1000);
            $page->click($planButtonElement);

            // プレミアムプランを選択して、宿泊予約入力画面に遷移する
            $premiumPlanButtonElement =
                'a[href="./reserve.html?plan-id=1"]';
            $page->waitFor($premiumPlanButtonElement);
            $page->click($premiumPlanButtonElement);
            $page->waitFor(2000);
            $pages = $browser->pages();
            $page = $pages[2];
            $page->setViewport(self::SCREEN_SIZES);

            // フォームを入力して宿泊予約入力画面に遷移する
            $page->waitForSelector("#date");
            $page->waitFor(1000);
            $date = $page->querySelector('#date');
            $date->click(["clickCount" => 3]);
            $page->waitFor(1000);
            $page->type("#date", "2020/08/06");
            $page->waitFor(1000);
            $planDesc = $page->querySelector('#plan-desc');
            $planDesc->click();
            $page->waitFor(1000);
            $term = $page->querySelector('#term');
            $term->click(["clickCount" => 3]);
            $page->waitFor(1000);
            $page->type("#term", "3");
            $page->waitFor(1000);
            $headCount = $page->querySelector('#head-count');
            $headCount->click(["clickCount" => 3]);
            $page->waitFor(1000);
            $page->type("#head-count", "4");
            $page->waitFor(1000);
            $page->click("#breakfast");
            $page->waitFor(1000);
            $page->click("#early-check-in");
            $page->waitFor(1000);
            $page->click("#sightseeing");
            $page->waitFor(1000);
            $page->select('#contact', 'email');
            $page->waitFor(1000);
            $page->click("#submit-button");
            $page->waitForSelector('#term');
            $page->waitFor(1000);

            // 予約完了した時は、予約完了のモーダルが
            // 表示されていることを確認する
            $page->click(
                'button[data-target="#success-modal"]'
            );
            $page->waitForSelector(
                'button[type="button"][class="btn btn-success"]'
            );
            $page->waitFor(1000);
            $closeButtonText = $page->querySelector(
                'button[type="button"][class="btn btn-success"]'
            )
                ->getProperty('textContent')
                ->jsonValue();
            $this->assertSame(
                '閉じる',
                $closeButtonText,
                '予約完了ができませんでした。'
                . '該当行数：'  . __LINE__ . '行目'
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
            foreach ($e->getTrace() as $trace) {
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