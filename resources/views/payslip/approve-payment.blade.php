<div class="card shadow-lg border-0">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0 text-white">Salary of: {{ $payslip->salary_month }}</h5>
    </div>
    <div class="card-body">

        <!-- Wrap table in a responsive container -->
        <div class="table-responsive">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <th>Amount:</th>
                        <td><h4 class="text-success">₦{{ number_format($payslip->totalNetPayble(), 2, '.', ',') }}</h4></td>
                    </tr>

                </tbody>
            </table>
        </div>

        <form id="reversal-form" action="{{ route('payslip.process.approval') }}" method="POST">
            @csrf

            <!-- OTP Input -->
            <div class="mb-3">
                <label for="otp" class="form-label"><strong>Enter OTP:</strong></label>
                <div class="input-group">
                    <input type="text" id="otp" name="otp" class="form-control" maxlength="6" placeholder="Enter 6-digit OTP" required>
                    <span class="input-group-text">
                        <i class="fas fa-key"></i> <!-- FontAwesome Icon -->
                    </span>
                </div>
                <input type='hidden' name='payslip_id' value="{{\Crypt::encrypt($payslip->id)}}">
                <small class="text-muted">A one-time password (OTP) has been sent to your email.</small>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary" id="submit-button" >
                    <span id="button-text">Approve Salary</span>
                    <span id="button-loader" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </form>
    </div>
</div>




<script>
    $(document).ready(function () {
        console.log("jQuery loaded and DOM ready"); // Debugging log

        const otpInput = $("#otp");
        const submitButton = $("#submit-button");
        const buttonText = $("#button-text");
        const buttonLoader = $("#button-loader");

        // Debugging: Log elements to ensure they are selected
        console.log(otpInput, submitButton, buttonText, buttonLoader);

        // Enable submit button only when OTP length is 6
        otpInput.on("input", function () {
            submitButton.prop("disabled", otpInput.val().length !== 6);
        });

        // Confirmation before submitting the form
        $("#reversal-form").on("submit", function (e) {
            e.preventDefault(); // Prevent form submission
            if (!confirm("Are you sure you want to proceed?")) {
                return; // Stop further execution
            }
            // Show loading spinner and disable button
            submitButton.prop("disabled", true);
            buttonText.text("Processing...");
            buttonLoader.removeClass("d-none");

            // Manually submit the form after confirmation
            this.submit();
        });
    });
</script>