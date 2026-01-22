<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Status Updated</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #F59E0B; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .ticket-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #F59E0B; }
        .status-change { background: #FEF3C7; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .status-badge { display: inline-block; padding: 8px 16px; border-radius: 16px; font-size: 14px; font-weight: 600; margin: 0 5px; }
        .status-open { background: #FEF3C7; color: #92400E; }
        .status-in_progress { background: #DBEAFE; color: #1E40AF; }
        .status-resolved { background: #D1FAE5; color: #065F46; }
        .status-closed { background: #F3F4F6; color: #374151; }
        .info-row { margin: 10px 0; }
        .label { font-weight: bold; color: #6B7280; }
        .value { color: #111827; }
        .arrow { font-size: 20px; color: #6B7280; margin: 0 10px; }
        .footer { text-align: center; padding: 20px; color: #6B7280; font-size: 14px; }
        .button { display: inline-block; padding: 12px 24px; background: #F59E0B; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 Ticket Status Updated</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $ticket->user->name }}</strong>,</p>
            
            <p>The status of your support ticket has been updated.</p>
            
            <div class="ticket-info">
                <div class="info-row">
                    <span class="label">Ticket Number:</span>
                    <span class="value"><strong>{{ $ticket->ticket_number }}</strong></span>
                </div>
                
                <div class="info-row">
                    <span class="label">Subject:</span>
                    <span class="value">{{ $ticket->subject }}</span>
                </div>
            </div>
            
            <div class="status-change">
                <p><strong>Status Change:</strong></p>
                <div>
                    <span class="status-badge status-{{ str_replace('_', '-', $oldStatus) }}">{{ ucwords(str_replace('_', ' ', $oldStatus)) }}</span>
                    <span class="arrow">→</span>
                    <span class="status-badge status-{{ str_replace('_', '-', $newStatus) }}">{{ ucwords(str_replace('_', ' ', $newStatus)) }}</span>
                </div>
            </div>
            
            @if($newStatus === 'resolved')
                <div style="background: #D1FAE5; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <p><strong>✅ Your ticket has been resolved!</strong></p>
                    <p>Our support team has marked this ticket as resolved. If you're satisfied with the resolution, no further action is needed.</p>
                    <p>If you need additional assistance, please reply to the ticket or create a new one.</p>
                </div>
            @elseif($newStatus === 'closed')
                <div style="background: #F3F4F6; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <p><strong>📁 Your ticket has been closed.</strong></p>
                    <p>This ticket has been closed. If you need further assistance, please create a new support ticket.</p>
                </div>
            @elseif($newStatus === 'in_progress')
                <div style="background: #DBEAFE; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <p><strong>🔧 Our team is working on your ticket.</strong></p>
                    <p>Your ticket is now being actively worked on by our support team. We'll update you as soon as we have more information.</p>
                </div>
            @endif
            
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
