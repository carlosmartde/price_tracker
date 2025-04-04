<?php
// app/Console/Commands/UpdateProductPrices.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PriceApiService;
use App\Models\PriceAlert;
use App\Models\User;
use App\Models\Product;
use App\Notifications\PriceAlertNotification;

class UpdateProductPrices extends Command
{
    protected $signature = 'prices:update';
    protected $description = 'Actualiza los precios de los productos desde las APIs';
    
    protected $priceApiService;
    
    public function __construct(PriceApiService $priceApiService)
    {
        parent::__construct();
        $this->priceApiService = $priceApiService;
    }
    
    public function handle()
    {
        $this->info('Iniciando actualización de precios...');
        
        // Actualizar precios desde la API
        $productsUpdated = $this->priceApiService->updateProductPrices();
        $this->info("Se actualizaron precios de {$productsUpdated} productos.");
        
        // Verificar alertas de precio
        $alertsTriggered = $this->checkPriceAlerts();
        $this->info("Se activaron {$alertsTriggered} alertas de precio.");
        
        $this->info('Actualización de precios completada.');
        
        return 0;
    }
    
    protected function checkPriceAlerts()
    {
        $this->info('Verificando alertas de precio...');
        
        // Obtener todas las alertas activas
        $alerts = PriceAlert::where('is_active', true)->get();
        $triggeredAlerts = 0;
        
        foreach ($alerts as $alert) {
            $product = Product::find($alert->product_id);
            
            if (!$product) {
                continue;
            }
            
            // Verificar si se cumple la condición de la alerta
            $isTriggered = false;
            
            switch ($alert->condition) {
                case 'below':
                    $isTriggered = $product->current_price <= $alert->target_price;
                    break;
                case 'above':
                    $isTriggered = $product->current_price >= $alert->target_price;
                    break;
                case 'percent_change':
                    if ($product->old_price > 0) {
                        $percentChange = (($product->current_price - $product->old_price) / $product->old_price) * 100;
                        $isTriggered = abs($percentChange) >= $alert->target_percent;
                    }
                    break;
            }
            
            // Si la alerta se activó, notificar al usuario
            if ($isTriggered) {
                $triggeredAlerts++;
                $user = User::find($alert->user_id);
                
                if ($user) {
                    $this->info("Enviando notificación para alerta #{$alert->id} al usuario {$user->email}");
                    $user->notify(new PriceAlertNotification($alert, $product));
                    
                    // Si la alerta es de una sola vez, desactivarla
                    if ($alert->is_one_time) {
                        $alert->is_active = false;
                        $alert->save();
                        $this->info("Alerta de una sola vez #{$alert->id} desactivada.");
                    }
                }
            }
        }
        
        return $triggeredAlerts;
    }
}