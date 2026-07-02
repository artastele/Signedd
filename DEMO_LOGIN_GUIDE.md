# SignED Demo Login Guide

Use existing verified demo accounts for the live capstone demo. Do not register a brand-new account during the presentation unless email/OTP delivery has already been tested.

## Recommended Strategy

- Start with the existing Parent account for the parent dashboard/enrollment/status view.
- Use the existing SPED Teacher account for verification, assessment, IEP meeting, PDSP, IEP, and lesson/activity setup.
- Use the existing Learner account for lesson viewing, activity completion, and progress display.
- Use Guidance and Principal accounts only when the demo script needs those signing/review perspectives.
- Keep Admin as a backup account for user/role visibility and troubleshooting.

## OTP / Mail Risk

Registration and login for unverified accounts can redirect to `/auth/verify-email`. That flow depends on mail delivery. Existing verified demo accounts avoid this blocker.

## Role Checklist

| Role | Use in demo | OTP status requirement |
| --- | --- | --- |
| Admin | Backup/admin overview | Must already be verified |
| Parent | Enrollment/status/progress | Must already be verified |
| Learner | Activity/progress flow | Must already be verified |
| SPED Teacher | Main workflow driver | Must already be verified |
| Guidance | IEP meeting/signing support | Must already be verified |
| Principal | Approval/signing support | Must already be verified |
| Master Teacher | Optional only | Must already be verified |

## Live Demo Rule

If OTP email is not confirmed working before the demo, do not use the registration flow as the primary path. Show the register page briefly if needed, then continue with the prepared verified Parent account.
