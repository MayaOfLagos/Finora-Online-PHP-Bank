<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket Created</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .ticket-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4F46E5; }
        .info-row { margin: 10px 0; }
        .label { font-weight: bold; color: #6B7280; }
        .value { color: #111827; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-priority-low { background: #D1FAE5; color: #065F46; }
        .badge-priority-medium { background: #FEF3C7; color: #92400E; }
        .badge-priority-high { background: #FEE2E2; color: #991B1B; }
        .badge-priority-urgent { background: #FEE2E2; color: #991B1B; }
        .badge-status { background: #DBEAFE; color: #1E40AF; }
        .footer { text-align: center; padding: 20px; color: #6B7280; font-size: 14px; }
        .button { display: inline-block; padding: 12px 24px; background: #4F46E5; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎫 Support Ticket Created</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $ticket->user->name }}</strong>,</p>
            
            <p>Your support ticket has been successfully created and our team has been notified. We'll review your request and get back to you as soon as possible.</p>
            
            <div class="ticket-info">
                <div class="info-row">
                    <span class="label">Ticket Number:</span>
                    <span class="value"><strong>{{ $ticket->ticket_number }}</strong></span>
                </div>
                
                <div class="info-row">
                    <span class="label">Subject:</span>
                    <span class="value">{{ $ticket->subject }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Category:</span>
                    <span class="value">{{ $ticket->category->name }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Priority:</span>
                    <span class="badge badge-priority-{{ $ticket->priority->value }}">{{ $ticket->priority->label() }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="badge badge-status">{{ $ticket->status->label() }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Created:</span>
                    <span class="value">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
            
            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Our support team will review your ticket</li>
                <li>You'll receive email notifications for any updates</li>
                <li>Average response time: 24-48 hours</li>
            </ul>
            
            <center>
                <a href="{{ config('app.url') }}/support/tickets/{{ $ticket->uuid }}" class="button">View Ticket</a>
            </center>
        </div>
        
        <div class="footer">
            <p>This is an automated email from Finora Bank Support System.</p>
            <p>Please do not reply to this email. For assistance, contact our support team.</p>
            <p>&copy; {{ date('Y') }} Finora Bank. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
