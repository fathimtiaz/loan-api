<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanTerm;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * request a new loan for an authenticated customer user.
     */
    public function request(Request $request)
    {
        $loan = $this->requestToLoan($request);
        
        $loan_terms = $this->loanToTerms($loan);

        DB::beginTransaction();

        try {
            $loan->save();

            for ($i=0; $i < $loan->number_of_terms; $i++) { 
                $loan_terms[$i]->save();
            }
             
            DB::commit();    
        } catch (\Exception $e) {
            report($e);
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'internal server error'
            ], 500);
        }

        $loan = Loan::where('id', $loan->id)->first();
        $loan->terms;

        return response()->json([
            'status' => 'success',
            'loan' => $loan
        ], 200);
    }

    /**
     * all a new loan for an authenticated customer user.
     */
    public function all(Request $request)
    {
        $user_id = auth()->user()->id;
        $user = User::where('id', $user_id)->first();

        try {
            if (!$user->isAdmin()) {
                $loans = Loan::where('user_id', $user_id);
            } else {
                $loans = Loan::all();
            }

            $page_items = ($request->query('page_items') != NULl) ? $request->query('page_items') : 10;
            $loans = $loans->paginate($page_items);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'loan not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'loans' => $loans->all(),
            'pagination' =>$loans
        ], 200);
    }

    /**
     * show a new loan for an authenticated customer user.
     */
    public function show(string $id, Request $request)
    {
        $user_id = auth()->user()->id;
        $user = User::where('id', $user_id)->first();
        
        try {
            $loan = Loan::where('id', $id);

            if (!$user->isAdmin()) {
                $loan = $loan->where('user_id', $user_id);
            }

            $loan = $loan->firstOrFail();
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'loan not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'loan' => $loan
        ], 200);
    }

    /**
     * approve a loan
     */
    public function approve(string $id)
    {
        try {
            $loan = Loan::where('id', $id)->firstOrFail();
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'loan not found'
            ], 404);
        }

        $loan->state = Loan::STATE_APPROVED;

        foreach($loan->terms as $loan_term)
        {
            $loan_term->state = Loan::STATE_APPROVED;
        }
        
        DB::beginTransaction();

        try {
            $loan->save();

            foreach($loan->terms as $loan_term)
            {
                $loan_term->save();
            }
             
            DB::commit();    
        } catch (\Exception $e) {
            report($e);
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'internal server error'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'loan' => $loan
        ], 200);
    }

    private function requestToLoan(Request $request)
    {
        $loan = new Loan;
        
        $loan->user_id = auth()->user()->id;
        $loan->total_amount = $request->amount;
        $loan->number_of_terms = $request->number_of_terms;
        $loan->state = Loan::STATE_PENDING;

        return $loan;
    }

    /**
     * loanToTerms new LoanTerm[] from Loan
     */
    private function loanToTerms(Loan $loan)
    {
        $loan_terms = array();
        $sum_amount_control = 0;
        $term_amount = round($loan->total_amount / $loan->number_of_terms, 2);

        for ($i=1; $i <= $loan->number_of_terms; $i++) { 
            $amount = $term_amount;
            $sum_amount_control = round($sum_amount_control+$amount, 2);
            
            if ($i == $loan->number_of_terms) {
                $amount = round($amount + ($loan->total_amount - $sum_amount_control), 2);
            }
            
            $loan_term = $this->loanToTerm($loan, $i, $amount);
            $loan_terms[] = $loan_term;
        }

        return $loan_terms;
    }

    /**
     * loanToTerm new LoanTerm from Loan
     */
    private static function loanToTerm(Loan $loan, $term_number, $amount)
    {
        $loan_term = new LoanTerm;

        $days_to_due_date = LoanTerm::TERM_INTERVAL_DAYS * $term_number;

        $loan_term->loan_id = $loan->id;
        $loan_term->amount = $amount;
        $loan_term->term_number = $term_number;
        $loan_term->due_date = now()->addDays($days_to_due_date);
        $loan_term->state = Loan::STATE_PENDING;

        return $loan_term;
    }
}
