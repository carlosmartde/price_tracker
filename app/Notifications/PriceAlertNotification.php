<?php
// app/Notifications/PriceAlertNotification.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PriceAlert;
use App\Models\Product;

class PriceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $alert;
    protected $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(PriceAlert $alert, Product $product)
    {
        $this->alert = $alert;
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('¡Alerta de Precio Activada!')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Una de tus alertas de precio se ha activado:');

        switch ($this->alert->condition) {
            case 'below':
                $message->line("El precio de {$this->product->name} ha bajado a {$this->product->current_price} {$this->product->currency}.");
                $message->line("Tu alerta se configuró para notificarte cuando el precio bajara de {$this->alert->target_price} {$this->product->currency}.");
                break;
            case 'above':
                $message->line("El precio de {$this->product->name} ha subido a {$this->product->current_price} {$this->product->currency}.");
                $message->line("Tu alerta se configuró para notificarte cuando el precio superara los {$this->alert->target_price} {$this->product->currency}.");
                break;
            case 'percent_change':
                $percentChange = round((($this->product->current_price - $this->product->old_price) / $this->product->old_price) * 100, 2);
                $direction = $percentChange > 0 ? 'subido' : 'bajado';
                $message->line("El precio de {$this->product->name} ha {$direction} un " . abs($percentChange) . "%.");
                $message->line("Precio anterior: {$this->product->old_price} {$this->product->currency}");
                $message->line("Precio actual: {$this->product->current_price} {$this->product->currency}");
                break;
        }

        return $message
            ->action('Ver Producto', url("/products/{$this->product->id}"))
            ->line('Gracias por usar nuestro servicio de seguimiento de precios.')
            ->line($this->alert->is_one_time ? 'Esta alerta ha sido desactivada automáticamente.' : 'Seguirás recibiendo notificaciones cuando se cumpla esta condición.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'old_price' => $this->product->old_price,
            'current_price' => $this->product->current_price,
            'condition' => $this->alert->condition,
            'target_price' => $this->alert->target_price,
            'target_percent' => $this->alert->target_percent,
        ];
    }
}