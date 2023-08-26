# whmcs-ishaarat
Ishaarat Whatsapp Module for WHMCS

- working well with WHMCS 8.7

Installation

    Upload files to your WHMCS root.
    Go to Admin Area. Enter Menu->Setup->Addon Modules and Activate Ishaarat Whatsapp
    After saving changes, give privigle to admin groups that you want at same page.
    Go to Menu->Setup->Custom Client Fields
    Add a field: name=Consent receive whatsapp, type= Tick box, Show on Order Form=check. (This field will be shown at register page. If user do not check this field, Whatsapp messages will not send to this user)

    Add a field: name=Mobile Number, type=Text Box, Show on Order Form=check. (This field will be shown at register page. Whatsapp will send to this value that user fills.)

    Enter Menu->Addons->Ishaarat Whatsapp
    Write your api details. or Get yours from https://ishaarat.com/pricing

Supported Hooks

    ClientChangePassword: Send whatsapp message to user if changes account password
    TicketAdminReply: Send whatsapp message to user if admin replies user's ticket
    ClientAdd: Send whatsapp message when user register
    AfterRegistrarRegistration: Send whatsapp message to user when domain registred succesfully
    AfterRegistrarRenewal: Send whatsapp message to user when domain renewed succesfully
    AfterModuleCreate_SharedAccount: Send whatsapp message to user when hosting account created.
    AfterModuleCreate_ResellerAccount: Send whatsapp message to user when reseller account created.
    AcceptOrder: Send whatsapp message to user when order accepted manually or automatically.
    DomainRenewalNotice: Remaining to the end of {x} days prior to the domain's end time, user will be get a message.
    InvoicePaymentReminder: If there is a payment that not paid, user will be get a information message.
    InvoicePaymentReminder_FirstOverdue: Invoice payment first for seconds overdue.
    InvoicePaymentReminder_secondoverdue: Invoice payment second for seconds overdue.
    InvoicePaymentReminder_thirdoverdue: Invoice payment third for seconds overdue.
    AfterModuleSuspend: Send whatsapp message after hosting account suspended.
    AfterModuleUnsuspend: Send whatsapp message after hosting account unsuspended.
    InvoiceCreated: Send whatsapp message every invoice creation.
    AfterModuleChangePassword: After module change password.
    InvoicePaid: Whenyou have paidthe billsends a message.
