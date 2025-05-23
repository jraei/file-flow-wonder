<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CalculatorController extends Controller
{
    /**
     * Display the winrate calculator page.
     *
     * @return \Inertia\Response
     */
    public function winrate()
    {
        return Inertia::render('Calculator/Winrate');
    }

    /**
     * Display the magic wheel calculator page.
     *
     * @return \Inertia\Response
     */
    public function magicWheel()
    {
        return Inertia::render('Calculator/MagicWheel');
    }

    /**
     * Display the zodiac calculator page.
     *
     * @return \Inertia\Response
     */
    public function zodiac()
    {
        return Inertia::render('Calculator/Zodiac');
    }

    /**
     * Calculate the required wins to reach a desired winrate.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateWinrate(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'totalMatches' => 'required|numeric|min:1',
            'currentWinRate' => 'required|numeric|min:0|max:100',
            'desiredWinRate' => 'required|numeric|min:0|max:100',
        ]);

        $totalMatches = $validated['totalMatches'];
        $currentWinRate = $validated['currentWinRate'];
        $desiredWinRate = $validated['desiredWinRate'];

        // Check if desired win rate is greater than current
        if ($desiredWinRate <= $currentWinRate) {
            return response()->json([
                'error' => 'Desired win rate must be higher than current win rate'
            ], 422);
        }

        // Calculate current wins
        $currentWins = ($currentWinRate / 100) * $totalMatches;

        // Check if calculation is possible (edge case)
        if ($desiredWinRate == 100) {
            $requiredWins = INF; // Handle 100% desired win rate (division by zero)
        } else {
            // Calculate required wins
            $requiredWins = ($desiredWinRate * $totalMatches - 100 * $currentWins) / (100 - $desiredWinRate);
        }

        // Round up to nearest whole number
        $requiredWins = ceil($requiredWins);

        return response()->json([
            'requiredWins' => $requiredWins,
            'message' => "You need about {$requiredWins} wins without losing to reach a {$desiredWinRate}% Win Rate."
        ]);
    }
}