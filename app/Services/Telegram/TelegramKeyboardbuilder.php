<?php

namespace App\Services\Telegram\Helpers;

use Telegram\Bot\Keyboard\Keyboard;

class TelegramKeyboardBuilder
{
    /**
     * Build main menu for regular employees
     */
    public static function employeeMainMenu(): Keyboard
    {
        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->setPersistent(true)
            ->row([
                Keyboard::button(['text' => '📍 Check In']),
                Keyboard::button(['text' => '👋 Check Out']),
            ])
            ->row([
                Keyboard::button(['text' => '📊 My Stats']),
                Keyboard::button(['text' => '🌴 My Leaves']),
            ]);
    }

    /**
     * Build main menu for HR managers
     */
    public static function hrMainMenu(int $pendingCandidates = 0): Keyboard
    {
        $candidateText = $pendingCandidates > 0
            ? "👥 Review Candidates ({$pendingCandidates})"
            : "👥 Review Candidates";

        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->setPersistent(true)
            ->row([
                Keyboard::button(['text' => '💼 Post New Job']),
                Keyboard::button(['text' => $candidateText]),
            ])
            ->row([
                Keyboard::button(['text' => '📊 Today\'s Stats']),
                Keyboard::button(['text' => '🌴 Who\'s Out Today']),
            ])
            ->row([
                Keyboard::button(['text' => '📢 Send Announcement']),
                Keyboard::button(['text' => '👤 Add Employee']),
            ])
            ->row([
                Keyboard::button(['text' => '📍 Check In']),
                Keyboard::button(['text' => '👋 Check Out']),
            ]);
    }

    /**
     * Build main menu for Super Admin
     */
    public static function adminMainMenu(?string $activeCompanyName = null): Keyboard
    {
        $companyText = $activeCompanyName
            ? "🏢 Company: {$activeCompanyName}"
            : "🏢 Select Company";

        $keyboard = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setPersistent(true)
            ->row([
                Keyboard::button(['text' => $companyText]),
            ]);

        // If company is selected, show HR actions
        if ($activeCompanyName) {
            $keyboard->row([
                Keyboard::button(['text' => '💼 Post New Job']),
                Keyboard::button(['text' => '👥 Review Candidates']),
            ])
                ->row([
                    Keyboard::button(['text' => '📊 Company Stats']),
                    Keyboard::button(['text' => '🌴 Who\'s Out']),
                ])
                ->row([
                    Keyboard::button(['text' => '👤 Add Employee']),
                    Keyboard::button(['text' => '📢 Announcement']),
                ]);
        }

        $keyboard->row([
            Keyboard::button(['text' => '📊 Global Stats']),
            Keyboard::button(['text' => '🏢 All Companies']),
        ]);

        return $keyboard;
    }

    /**
     * Location request button
     */
    public static function requestLocation(string $buttonText = '📍 Share My Location'): Keyboard
    {
        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button([
                    'text' => $buttonText,
                    'request_location' => true
                ])
            ])
            ->row([
                Keyboard::button(['text' => '❌ Cancel'])
            ]);
    }

    /**
     * Contact (phone) request button for registration
     */
    public static function requestContact(string $buttonText = '📱 Share My Contact'): Keyboard
    {
        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button([
                    'text' => $buttonText,
                    'request_contact' => true
                ])
            ]);
    }

    /**
     * Cancel button (inline)
     */
    public static function cancelButton(): Keyboard
    {
        return Keyboard::make()->inline()->row([
            Keyboard::inlineButton(['text' => '❌ Cancel', 'callback_data' => 'cancel_wizard'])
        ]);
    }

    /**
     * Back to menu button
     */
    public static function backToMenu(): Keyboard
    {
        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->row([
                Keyboard::button(['text' => '🏠 Main Menu'])
            ]);
    }

    /**
     * Language selection buttons
     */
    public static function languageSelection(): Keyboard
    {
        return Keyboard::make()->inline()
            ->row([
                Keyboard::inlineButton(['text' => "🇺🇿 O'zbekcha", 'callback_data' => 'lang_uz']),
                Keyboard::inlineButton(['text' => "🇬🇧 English", 'callback_data' => 'lang_en']),
            ])
            ->row([
                Keyboard::inlineButton(['text' => "🇷🇺 Русский", 'callback_data' => 'lang_ru']),
            ]);
    }

    /**
     * Yes/No confirmation buttons (inline)
     */
    public static function yesNoButtons(string $yesCallback, string $noCallback): Keyboard
    {
        return Keyboard::make()->inline()->row([
            Keyboard::inlineButton(['text' => '✅ Yes', 'callback_data' => $yesCallback]),
            Keyboard::inlineButton(['text' => '❌ No', 'callback_data' => $noCallback]),
        ]);
    }

    /**
     * Skip button (inline)
     */
    public static function skipButton(string $callback = 'skip_step'): Keyboard
    {
        return Keyboard::make()->inline()->row([
            Keyboard::inlineButton(['text' => '⏭ Skip', 'callback_data' => $callback])
        ]);
    }

    /**
     * Remove keyboard (hide custom keyboard)
     */
    public static function removeKeyboard(): Keyboard
    {
        return Keyboard::remove();
    }

    /**
     * Generic inline keyboard from array of buttons
     *
     * @param array $buttons Format: [['text' => 'Button', 'callback' => 'data'], ...]
     * @param int $buttonsPerRow Number of buttons per row
     */
    public static function inlineGrid(array $buttons, int $buttonsPerRow = 2): Keyboard
    {
        $keyboard = Keyboard::make()->inline();
        $chunks = array_chunk($buttons, $buttonsPerRow);

        foreach ($chunks as $row) {
            $rowButtons = array_map(function ($btn) {
                return Keyboard::inlineButton([
                    'text' => $btn['text'],
                    'callback_data' => $btn['callback']
                ]);
            }, $row);

            $keyboard->row($rowButtons);
        }

        return $keyboard;
    }
}
