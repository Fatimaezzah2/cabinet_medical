Hello,

This appointment has been confirmed.

Patient: {{ $appointment->patient->name }}
Doctor: {{ $appointment->doctor->name }}
Service: {{ $appointment->service->name }}
Date: {{ $appointment->appointment_date->format('Y-m-d') }}
Time: {{ substr($appointment->appointment_time, 0, 5) }}
Total price: {{ number_format((float) $appointment->total_price, 2) }}

Thank you.
