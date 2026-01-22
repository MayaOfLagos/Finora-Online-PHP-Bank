<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Reply on Your Ticket</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10B981; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .ticket-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10B981; }
        .message-box { background: #F3F4F6; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #6B7280; }
        .info-row { margin: 10px 0; }
        .label { font-weight: bold; color: #6B7280; }
        .value { color: #111827; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-agent { background: #DBEAFE; color: #1E40AF; }
        .footer { text-align: center; padding: 20px; color: #6B7280; font-size: 14px; }
        .button { display: inline-block; padding: 12px 24px; background: #10B981; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 New Reply on Your Ticket</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $ticket->user->name }}</strong>,</p>
            
            <p>A support agent has replied to your ticket <strong>#{{ $ticket->ticket_number }}</strong>.</p>
            
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
                    <span class="label">Replied By:</span>
                    <span class="badge badge-agent">Support Agent</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Replied At:</span>
                    <span class="value">{{ $ticketMessage->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
            
            <div class="message-box">
                <p><strong>Agent's Reply:</strong></p>
                <p>{{ $ticketMessage->message }}</p>
            </div>
            
            <p>Please log in to your account to view the full conversation and respond if needed.</p>
            
            <center>
                <a href="{{ config('app.url') }}/support/tickets/{{ $ticket->uuid }}" class="button">View & Respond</a>
            </center>
        </div>
        
        <div class="footer">
            <p>This is an automated email from Finora Bank Support System.</p>
            <p>Please do not reply to this email. Use the link above to respond to your ticket.</p>
            <p>&copy; {{ date('Y') }} Finora Bank. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
