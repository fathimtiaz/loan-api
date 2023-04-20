<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanTerm;
use App\Models\Repayment;
use Illuminate\Support\Facades\DB;

class RepaymentController extends Controller
{
    /**
     * repay a loan
     * will repay any unpaid loan terms regardless of the terms schedule
     * repayment amount should be bigger than currently unpaid loan terms amount
     * bigger repayment amount will be distributed to next loan terms
     * any overpay will be returned in the response
     */
    public function repay(string $loan_id, Request $request)
    {
        try {
            $loan = Loan::where('id', $loan_id)
                ->firstOrFail();
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'loan not found'
            ], 404);
        }

        if ($loan->state == Loan::STATE_PENDING) {
            return response()->json([
                'status' => 'error',
                'message' => 'loan is not approved'
            ], 404);
        }

        if ($loan->state == Loan::STATE_PAID) {
            return response()->json([
                'status' => 'error',
                'message' => 'loan already paid'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $overpay = $this->repayLoanTerms($request, $loan);

            $repayment = $this->requestToRepayment($loan_id, $request->amount);
        
            $loan->save();

            $repayment->save();
             
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
            'overpay' => ($overpay>0) ? $overpay : 0,
            'loan' => $loan
        ], 200);
    }

    private function requestToRepayment(string $loan_id, float $amount)
    {
        $repayment = new Repayment;

        $repayment->loan_id = $loan_id;
        $repayment->amount = $amount;

        return $repayment;
    }

    private function repayLoanTerms(Request $request, Loan &$loan)
    {
        $repay_amount = $request->amount; 

        while ($repay_amount > 0) 
        {
            $this_loan_term = $loan->terms()
                ->where('loan_id', $loan->id)
                ->where('state', '!=', Loan::STATE_PAID)
                ->orderBy('term_number')
                ->first();

            if ($this_loan_term == NULL) break;

            $this_loan_term_unpaid_amount = $this_loan_term->amount - $this_loan_term->paid_amount;
            $this_repay_amount = ($repay_amount > $this_loan_term_unpaid_amount) ? $this_loan_term_unpaid_amount : $repay_amount;

            // repay amount should be more than current scheduled amount
            if ($repay_amount == $request->amount && $repay_amount < $this_loan_term_unpaid_amount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'repayment amount is less than scheduled amount'
                ], 400);
            }

            $this_loan_term->paid_amount += $this_repay_amount;
            $this_loan_term->state = ($this_loan_term->amount == $this_loan_term->paid_amount) ? Loan::STATE_PAID : Loan::STATE_APPROVED;

            $this_loan_term->save();

            if ($this_loan_term->term_number == $loan->number_of_terms && $this_loan_term->state == Loan::STATE_PAID) {
                $loan->state = Loan::STATE_PAID;
            }
    
            $repay_amount -= $this_loan_term_unpaid_amount;
        }

        return $repay_amount;
    }
}
