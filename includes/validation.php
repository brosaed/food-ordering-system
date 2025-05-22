<?php

/**
 * Sanitize input data
 */
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number
 */
function validatePhone($phone)
{
    return preg_match('/^\+?[\d\s\-]{10,}$/', $phone);
}

/**
 * Validate name
 */
function validateName($name)
{
    return preg_match('/^[a-zA-Z\s\-]{2,100}$/', $name);
}

/**
 * Validate password strength
 */
function validatePassword($password)
{
    return strlen($password) >= 8;
}

/**
 * Validate positive number
 */
function validatePositiveNumber($number)
{
    return filter_var($number, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}

/**
 * Validate decimal number
 */
function validateDecimal($number)
{
    return filter_var($number, FILTER_VALIDATE_FLOAT);
}

/**
 * Validate date
 */
function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}


/**
 * Validate username format
 */
function validateUsername($username)
{
    return preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username);
}


function validatePromoCode($promoCode, $subtotal)
{
    global $pdo;

    // Query to check the promo code conditions
    $stmt = $pdo->prepare("SELECT * FROM promo_codes 
                           WHERE code = ? 
                           AND valid_from <= NOW() 
                           AND valid_until >= NOW() 
                           AND (min_order_amount <= ? OR min_order_amount = 0)
                           AND (use_limit IS NULL OR use_count < use_limit)
                           LIMIT 1");
    $stmt->execute([$promoCode, $subtotal]);

    $promo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($promo) {
        $discountAmount = 0;

        // Check the discount type (fixed or percentage)
        if ($promo['discount_type'] === 'percentage') {
            $discountAmount = $subtotal * ($promo['discount_value'] / 100);
        } else if ($promo['discount_type'] === 'fixed') {
            $discountAmount = $promo['discount_value'];
        }

        // Ensure the discount doesn't exceed the total order amount
        if ($discountAmount > $subtotal) {
            $discountAmount = $subtotal;
        }

        // Return the result with promo details
        return [
            'valid' => true,
            'promo' => $promo,
            'discount_amount' => $discountAmount,
            'message' => 'Promo code applied successfully!'
        ];
    } else {
        return [
            'valid' => false,
            'message' => 'Invalid promo code or the promo code has expired or been used up.'
        ];
    }
}

function applyPromoCode($promoCode, $subtotal, $orderId)
{
    // Validate promo code
    $promoValidation = validatePromoCode($promoCode, $subtotal);

    if ($promoValidation['valid']) {
        // Get the discount amount
        $discountAmount = $promoValidation['discount_amount'];

        // Calculate the final total after applying the discount
        $finalTotal = $subtotal - $discountAmount;

        // Update the order's total price in the database
        global $pdo;
        $stmt = $pdo->prepare("UPDATE orders SET total_amount = ? WHERE id = ?");
        $stmt->execute([$finalTotal, $orderId]);

        // Optional: Update the promo code usage count
        $promoId = $promoValidation['promo']['id'];
        $stmt = $pdo->prepare("UPDATE promo_codes SET use_count = use_count + 1 WHERE id = ?");
        $stmt->execute([$promoId]);

        return [
            'valid' => true,
            'final_total' => $finalTotal,
            'discount_amount' => $discountAmount,
            'message' => 'Promo code applied successfully! Discount deducted from total.'
        ];
    } else {
        return $promoValidation; // Return the existing invalid promo code response
    }
}




// function validatePromoCode($code, $subtotal)
// {
//     global $pdo;

//     try {
//         $stmt = $pdo->prepare("SELECT * FROM promo_codes 
//                               WHERE code = ? 
//                               AND valid_from <= NOW() 
//                               AND valid_until >= NOW()
//                               AND (use_limit IS NULL OR use_count < use_limit)");
//         $stmt->execute([$code]);
//         $promo = $stmt->fetch(PDO::FETCH_ASSOC);

//         if (!$promo) {
//             return ['valid' => false, 'message' => 'Invalid promo code'];
//         }

//         if ($subtotal < $promo['min_order_amount']) {
//             return ['valid' => false, 'message' => 'Minimum order amount not met'];
//         }

//         return [
//             'valid' => true,
//             'promo' => $promo,
//             'message' => 'Discount applied!',
//             'discount_amount' => calculateDiscount($promo, $subtotal)
//         ];
//     } catch (PDOException $e) {
//         error_log("Promo code validation error: " . $e->getMessage());
//         return ['valid' => false, 'message' => 'Error validating promo code'];
//     }
// }

function calculateDiscount($promo, $subtotal)
{
    if ($promo['discount_type'] === 'percentage') {
        return round($subtotal * ($promo['discount_value'] / 100), 2);
    }
    return min($promo['discount_value'], $subtotal);
}
