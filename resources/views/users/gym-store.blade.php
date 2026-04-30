@php
    $coverImageUrl = $business->cover_path
        ? route('users.gym.media', ['business' => $business, 'type' => 'cover'])
        : null;

    $logoImageUrl = $business->logo_path
        ? route('users.gym.media', ['business' => $business, 'type' => 'logo'])
        : null;

    $businessDisplayName = $business->user->business_name
        ?: $business->business_name
        ?: 'Business Name';

    $manualClosedMarker = '[[GYM_MANUAL_CLOSED]]';
    $facebookPageMarkerPattern = '/\[\[GYM_FACEBOOK_PAGE:(.*?)\]\]/';
    $rawNotes = (string) ($business->notes ?? '');
    $isGymManuallyClosed = is_string($business->notes) && str_contains($business->notes, $manualClosedMarker);
    $facebookPageLinkForDisplay = null;
    if (preg_match($facebookPageMarkerPattern, $rawNotes, $facebookPageMatch)) {
        $facebookPageLinkForDisplay = trim((string) ($facebookPageMatch[1] ?? ''));
    }
    $notesWithoutFacebookMarker = trim((string) preg_replace($facebookPageMarkerPattern, '', $rawNotes));
    $notesForDisplay = trim(str_replace($manualClosedMarker, '', $notesWithoutFacebookMarker));
    $notesForDisplay = $notesForDisplay !== '' ? $notesForDisplay : null;

    $businessBio = $notesForDisplay ?: 'Welcome to our gym.';

    $locationDisplay = $business->user->location ?: 'Location not provided';
    $openingTimeDisplay = $business->opening_time ? \Carbon\Carbon::createFromFormat('H:i:s', $business->opening_time)->format('h:i A') : null;
    $closingTimeDisplay = $business->closing_time ? \Carbon\Carbon::createFromFormat('H:i:s', $business->closing_time)->format('h:i A') : null;
    $openingHoursDisplay = ($openingTimeDisplay && $closingTimeDisplay)
        ? 'Mon-Sun: ' . $openingTimeDisplay . ' - ' . $closingTimeDisplay
        : 'Opening time not provided';
    $membershipDisplay = $business->capacity_limit
        ? 'Up to ' . $business->capacity_limit . ' members'
        : 'Capacity not set';
    $currentCapacityValue = (int) ($reservationCount ?? 0);
    $capacityStatusDisplay = $business->capacity_limit
        ? $currentCapacityValue . ' / ' . $business->capacity_limit
        : $currentCapacityValue . ' / maximum capacity set by users';
    $gymStatusValue = strtolower(trim((string) ($business->status ?? '')));
    if (!in_array($gymStatusValue, ['open', 'closed'], true)) {
        $gymStatusValue = $isGymManuallyClosed ? 'closed' : 'open';
    }

    $gymStatusLabel = $gymStatusValue === 'closed' ? 'Closed' : 'Open';
    $isReservationAllowed = $gymStatusValue !== 'closed';
    $reserveDisabled = ($hasReserved ?? false) || !$isReservationAllowed;
    $reserveButtonLabel = ($hasReserved ?? false)
        ? 'Already Reserved'
        : (!$isReservationAllowed ? 'Gym is Closed' : 'Reserve to this Gym');
    $hasPendingStorePayment = $hasPendingStorePayment ?? false;
    $canBuyStoreProducts = !$hasPendingStorePayment;
    $entranceFeeDisplay = $business->entrance_fee !== null
        ? '₱' . number_format((float) $business->entrance_fee) . ' pesos'
        : 'Not set';
    $monthlyAccessDisplay = $business->monthly_access !== null
        ? '₱' . number_format((float) $business->monthly_access) . ' pesos'
        : 'Not set';
    $rulesDisplay = $business->rules ?: 'No gym rules provided yet.';
    $extractStock = static function (?string $description): int {
        $pattern = '/\[\[STOCK:(\d+)\]\]/';
        if (!is_string($description) || !preg_match($pattern, $description, $matches)) {
            return 0;
        }

        return max(0, (int) ($matches[1] ?? 0));
    };
    $userSubscriptionOrders = $userSubscriptionOrders ?? collect();
    $subscriptionBadgesByType = [];
    foreach ($userSubscriptionOrders as $subscriptionOrder) {
        $subscription = $subscriptionOrder->subscription;
        if (!$subscription?->expires_at) {
            continue;
        }

        $typeKey = $subscription->subscription_type === 'gym_trainer' ? 'gym_trainer' : 'gym_access';
        if (array_key_exists($typeKey, $subscriptionBadgesByType)) {
            continue;
        }

        $expiresAt = \Carbon\Carbon::parse($subscription->expires_at);
        $daysLeft = max(0, now()->startOfDay()->diffInDays($expiresAt->startOfDay(), false));
        $subscriptionBadgesByType[$typeKey] = [
            'label' => $typeKey === 'gym_trainer' ? 'Trainer' : 'Access',
            'icon' => $typeKey === 'gym_trainer' ? 'fas fa-user-tie' : 'fas fa-crown',
            'days_label' => $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's') . ' left',
            'class' => $typeKey === 'gym_trainer' ? 'is-trainer' : 'is-access',
        ];
    }
    // Reorder subscriptions: gym_access first, then gym_trainer
    $subscriptionBadges = [];
    if (isset($subscriptionBadgesByType['gym_access'])) {
        $subscriptionBadges[] = $subscriptionBadgesByType['gym_access'];
    }
    if (isset($subscriptionBadgesByType['gym_trainer'])) {
        $subscriptionBadges[] = $subscriptionBadgesByType['gym_trainer'];
    }
    $showSubscribedBadge = !empty($subscriptionBadges);
    $hasSubscriptionAccess = (bool) ($hasSubscriptionAccess ?? false);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>GYM PROFILE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    @vite(['resources/css/business-settings.css'])
    <style>
        .store-products-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }
        .modal-close-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }
        .modal-close-btn {
            border: none;
            border-radius: 999px;
            padding: 7px 12px;
            background: #1f2b22;
            color: #e4e6eb;
            font-size: 0.76rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
        }
        .modal-close-btn:hover {
            background: #2b3a30;
            color: #fde68a;
        }
        .gym-status-btn[disabled] {
            opacity: 1;
            cursor: default;
        }
        .gym-status-btn.is-unavailable {
            background: #4b5563;
            border-color: #6b7280;
            color: #e5e7eb;
        }
        .avatar-wrapper {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .subscription-crown-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 55%, #f59e0b 100%);
            color: #3f2a00;
            border: 1px solid rgba(146, 64, 14, 0.35);
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 0.72rem;
            font-weight: 900;
            line-height: 1;
            box-shadow: 0 8px 14px rgba(245, 158, 11, 0.2);
            white-space: nowrap;
        }
        .subscription-badge-row {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .subscription-crown-badge i {
            font-size: 0.7rem;
        }
        .subscription-crown-badge.is-trainer {
            background: #0b0b0b;
            border-color: #1f2937;
            color: #facc15;
            box-shadow: 0 8px 14px rgba(2, 6, 23, 0.38);
        }
        #subscriptionsSection .subscription-card.is-trainer .subscription-icon-box {
            background: #0b0b0b;
            border: 1px solid #1f2937;
        }
        #subscriptionsSection .subscription-card.is-trainer .subscription-icon-box i {
            color: #facc15;
        }
        .reservation-payment-note {
            background: rgba(251, 191, 36, 0.12);
            border: 1px solid rgba(251, 191, 36, 0.36);
            border-radius: 10px;
            color: #fde68a;
            font-size: 0.8rem;
            padding: 8px 10px;
            margin-bottom: 10px;
        }
        .store-empty,
        .subscription-empty {
            color: #b0b3b8;
            font-size: 0.85rem;
            padding: 8px 2px;
        }
        .reservation-panel {
            display: flex;
            flex-direction: column;
            gap: 14px;
            background: linear-gradient(180deg, rgba(19, 33, 23, 0.68) 0%, rgba(15, 27, 19, 0.88) 100%);
            border: 1px solid #1f2b22;
            border-radius: 14px;
            padding: 16px;
        }
        .reservation-note {
            color: #d1d5db;
            font-size: 0.9rem;
        }
        .reservation-meta {
            color: #9ca3af;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .reserve-btn {
            border: none;
            border-radius: 999px;
            padding: 10px 16px;
            background: #fbbf24;
            color: #07130b;
            font-size: 0.85rem;
            font-weight: 800;
            width: fit-content;
            cursor: pointer;
            transition: transform 0.15s ease, filter 0.2s ease;
        }
        .reserve-btn:hover {
            filter: brightness(1.06);
            transform: translateY(-1px);
        }
        .reserve-btn[disabled] {
            background: #374151;
            color: #d1d5db;
            cursor: default;
            transform: none;
            filter: none;
        }
        .cancel-reserve-launch-btn {
            border: none;
            border-radius: 999px;
            padding: 10px 16px;
            background: #ef4444;
            color: #ffffff;
            font-size: 0.84rem;
            font-weight: 800;
            width: fit-content;
            cursor: pointer;
            transition: transform 0.15s ease, filter 0.2s ease;
        }
        .cancel-reserve-launch-btn:hover {
            filter: brightness(1.06);
            transform: translateY(-1px);
        }
        .cancel-reserve-btn {
            border: none;
            border-radius: 999px;
            padding: 10px 16px;
            background: #ef4444;
            color: #ffffff;
            font-size: 0.84rem;
            font-weight: 800;
            width: fit-content;
            cursor: pointer;
            transition: transform 0.15s ease, filter 0.2s ease;
        }
        .cancel-reserve-btn:hover {
            filter: brightness(1.06);
            transform: translateY(-1px);
        }
        .cancel-reserve-btn[disabled] {
            background: #4b5563;
            color: #d1d5db;
            cursor: default;
            transform: none;
            filter: none;
        }
        .reservation-status {
            margin-top: 10px;
            color: #86efac;
            font-size: 0.85rem;
            font-weight: 700;
        }
        .reservation-status.reservation-warning {
            color: #fca5a5;
        }
        .subscription-card {
            background: #242526;
            border: 1px solid #1f2b22;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.18);
        }
        .subscription-icon-box {
            width: 100%;
            height: 140px;
            background: #fbbf24;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #07130b;
            object-fit: cover;
        }
        .subscription-icon-box i {
            font-size: 3rem;
        }
        .subscription-card-body {
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: linear-gradient(180deg, rgba(19, 33, 23, 0.68) 0%, rgba(15, 27, 19, 0.88) 100%);
        }
        .subscription-duration {
            color: #e4e6eb;
            font-weight: 700;
        }
        .subscription-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
        }
        .subscription-price {
            color: #07130b;
            font-weight: 700;
            font-size: 0.9rem;
            background: #fbbf24;
            border-radius: 999px;
            padding: 6px 10px;
            display: inline-flex;
            width: fit-content;
        }
        .subscription-expire {
            color: #b0b3b8;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .buy-product-btn {
            border: none;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 0.75rem;
            font-weight: 800;
            font-family: inherit;
            cursor: pointer;
            background: #22c55e;
            color: #052e16;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .buy-product-btn:hover {
            background: #16a34a;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(34, 197, 94, 0.28);
        }
        .buy-product-btn:disabled {
            background: #4b5563;
            color: #d1d5db;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .buy-product-btn.is-blocked {
            background: #f59e0b;
            color: #1f1300;
            cursor: pointer;
        }
        .payment-choice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .payment-choice {
            border: 1px solid #2b3a30;
            border-radius: 12px;
            padding: 10px;
            background: #0f1b13;
            color: #e4e6eb;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 700;
        }
        .payment-choice.active {
            border-color: #fbbf24;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.2);
        }
        .payment-choice.is-disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }
        #gcashFields[hidden] {
            display: none !important;
        }
        #storeSection .product-image-wrap {
            position: relative;
            overflow: hidden;
        }
        #storeSection .stock-badge {
            position: absolute;
            top: 9px;
            right: 9px;
            background: linear-gradient(135deg, rgba(255, 249, 196, 0.98) 0%, rgba(253, 224, 71, 0.97) 52%, rgba(250, 204, 21, 0.97) 100%);
            color: #2a1400;
            border-radius: 999px;
            padding: 5px 11px;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.35px;
            text-transform: uppercase;
            box-shadow: 0 8px 18px rgba(245, 158, 11, 0.34), inset 0 1px 0 rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(122, 55, 0, 0.2);
            backdrop-filter: blur(2px);
            z-index: 2;
        }
        #storeSection .stock-badge::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #ca8a04;
            display: inline-block;
            margin-right: 6px;
            box-shadow: 0 0 0 2px rgba(202, 138, 4, 0.18);
        }
        #storeSection .product-card.sold .stock-badge {
            background: linear-gradient(135deg, rgba(254, 243, 199, 0.96) 0%, rgba(252, 211, 77, 0.96) 100%);
            color: #3f2a00;
            border-color: rgba(146, 64, 14, 0.28);
            box-shadow: 0 8px 16px rgba(180, 83, 9, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.45);
        }
        #storeSection .product-card.sold .stock-badge::before {
            background: #a16207;
            box-shadow: 0 0 0 2px rgba(161, 98, 7, 0.2);
        }
        @media (max-width: 1200px) {
            .store-products-grid,
            .subscriptions-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 900px) {
            .store-products-grid,
            .subscriptions-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .store-products-grid,
            .subscriptions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="profile-container" id="gymProfileApp">
    <div class="top-nav">
        <a href="{{ route('users.nearest-gym') }}" class="back-home-btn" aria-label="Back to Map" title="Back to Map"><i class="fas fa-arrow-left"></i></a>
    </div>

    <div class="cover-area">
        @if($coverImageUrl)
            <img class="cover-image" src="{{ $coverImageUrl }}" alt="cover">
        @else
            <div class="cover-empty">
                <i class="fas fa-image" aria-hidden="true"></i>
                <span>No cover image</span>
            </div>
        @endif
    </div>

    <div class="profile-pic-section">
        <div class="avatar-wrapper">
            @if($logoImageUrl)
                <img class="profile-img" src="{{ $logoImageUrl }}" alt="profile">
            @else
                <div class="profile-img-empty">
                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                    <span>No logo</span>
                </div>
            @endif
            @if($showSubscribedBadge)
                <div class="subscription-badge-row" aria-label="Active subscription durations">
                    @foreach($subscriptionBadges as $subscriptionBadge)
                        <div class="subscription-crown-badge {{ $subscriptionBadge['class'] }}">
                            <i class="{{ $subscriptionBadge['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $subscriptionBadge['label'] }}: {{ $subscriptionBadge['days_label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="profile-info">
            <div class="profile-name">{{ $businessDisplayName }}</div>
            <div class="profile-bio">{{ $businessBio }}</div>
            <div class="gym-open-close" aria-label="Gym status">
                <div class="gym-capacity-indicator"><i class="fas fa-users"></i> {{ $capacityStatusDisplay }}</div>
                <button type="button" class="gym-status-btn {{ $gymStatusValue === 'closed' ? 'is-closed' : 'is-open' }}" disabled>
                    <i class="fas {{ $gymStatusValue === 'closed' ? 'fa-door-closed' : 'fa-door-open' }}"></i> {{ $gymStatusLabel }}
                </button>
            </div>
        </div>
    </div>

    <div class="details-top-nav" aria-label="Gym page navigation">
        <button type="button" class="details-top-nav-link active" id="homeNavBtn">Home</button>
        <button type="button" class="details-top-nav-link" id="reservationNavBtn">Reservation</button>
        <button type="button" class="details-top-nav-link" id="storeNavBtn">Store</button>
        <button type="button" class="details-top-nav-link" id="subscriptionsNavBtn">Subscriptions</button>
    </div>

    <div class="details-section" id="reservationSection" style="display: none;">
        <div class="reservation-section-header">
            <div class="about-gym-label">Reservation</div>
        </div>

        <div class="reservation-panel">
            <div class="reservation-note">Reserve your gym visit slot before coming in.</div>
            <div class="reservation-meta">Current reservations: {{ $reservationCount ?? 0 }}</div>
            <form method="POST" action="{{ route('users.gym.reserve', $business) }}" id="reserveForm">
                @csrf
                @if($hasSubscriptionAccess)
                    <button type="submit" class="reserve-btn" {{ $reserveDisabled ? 'disabled' : '' }}>
                        {{ $reserveButtonLabel }}
                    </button>
                @else
                    <button type="button" id="openReservePaymentModalBtn" class="reserve-btn" {{ $reserveDisabled ? 'disabled' : '' }}>
                        {{ $reserveButtonLabel }}
                    </button>
                @endif
            </form>
            @if($hasReserved ?? false)
                <button type="button" class="cancel-reserve-launch-btn" id="openCancelReservationModalBtn">
                    Cancel Reservation
                </button>
            @endif
            @if(!$isReservationAllowed)
                <div class="reservation-status reservation-warning">Gym is currently closed. You can reserve when it opens.</div>
            @endif
            @if(session('status'))
                <div class="reservation-status {{ session('status_type') === 'error' ? 'reservation-warning' : '' }}">{{ session('status') }}</div>
            @endif
        </div>
    </div>

    <div class="details-section" id="aboutGymSection">
        <div class="about-header">
            <div class="about-gym-label">About this gym</div>
        </div>
        <div class="details-title"><i class="fas fa-dumbbell"></i> GYM DETAILS</div>
        <div class="details-grid">
            @if($facebookPageLinkForDisplay)
                <div class="detail-item"><i class="fab fa-facebook" style="color: #1877f2;"></i> <strong>Facebook Page</strong> <span>
                    <a href="{{ $facebookPageLinkForDisplay }}" target="_blank" rel="noopener noreferrer" style="color: #1877f2; text-decoration: none; font-weight: 500; transition: color 0.2s ease;" onmouseover="this.style.color='#165ce0'" onmouseout="this.style.color='#1877f2'">{{ $facebookPageLinkForDisplay }}</a>
                </span></div>
            @endif
            <div class="detail-item"><i class="fas fa-location-dot"></i> <strong>Location</strong> <span>{{ $locationDisplay }}</span></div>
            <div class="detail-item"><i class="fas fa-clock"></i> <strong>Opening Hours</strong> <span>{{ $openingHoursDisplay }}</span></div>
            <div class="detail-item"><i class="fas fa-signal"></i> <strong>Gym Status</strong> <span>{{ $gymStatusLabel }}</span></div>
            <div class="detail-item"><i class="fas fa-users"></i> <strong>Gym Capacity</strong> <span>{{ $membershipDisplay }}</span></div>
            <div class="detail-item"><i class="fas fa-door-open"></i> <strong>Entrance Fee</strong> <span>{{ $entranceFeeDisplay }}</span></div>
            <div class="detail-item"><i class="fas fa-credit-card"></i> <strong>Monthly Access</strong> <span>{{ $monthlyAccessDisplay }}</span></div>
        </div>
    </div>

    <div class="rules-section" id="rulesSection">
        <div class="rules-section-header">
            <div class="rules-title"><i class="fas fa-file-contract"></i> Gym Rules</div>
        </div>
        <div class="rules-content">{{ $rulesDisplay }}</div>
    </div>

    <div class="details-section" id="storeSection" style="display: none;">
        <div class="store-section-header">
            <div class="about-gym-label">Gym Store</div>
        </div>
        @if($products->isEmpty())
            <div class="store-empty">No products available yet.</div>
        @else
            <div class="store-products-grid">
                @foreach($products as $product)
                    @php
                        $productStock = $extractStock($product->description);
                        $isSoldOut = $productStock <= 0;
                    @endphp
                    <article class="product-card {{ $isSoldOut ? 'sold' : '' }}">
                        <div class="product-image-wrap">
                            <img class="product-image" src="{{ route('users.gym.products.media', ['business' => $business, 'product' => $product]) }}" alt="{{ $product->name }}">
                            <span class="stock-badge">Stock: {{ $productStock }}</span>
                        </div>
                        <div class="product-card-body">
                            <div class="product-card-head">
                                <div class="product-name">{{ $product->name }}</div>
                                <div class="product-state {{ $isSoldOut ? 'sold' : '' }}">{{ $isSoldOut ? 'Sold Out' : 'Available' }}</div>
                            </div>
                            <div class="product-footer">
                                <div class="product-price">₱{{ number_format((float) $product->price, 2) }} pesos</div>
                                <button
                                    type="button"
                                    class="buy-product-btn open-buy-modal-btn"
                                    data-action="{{ route('users.gym.products.order', ['business' => $business, 'product' => $product]) }}"
                                    data-product-name="{{ e($product->name) }}"
                                    data-product-price="{{ number_format((float) $product->price, 2) }}"
                                    {{ $isSoldOut ? 'disabled' : '' }}
                                >
                                    <i class="fas fa-cart-shopping"></i> Buy
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <div class="about-modal-overlay" id="buyProductModalOverlay">
        <div class="about-modal">
            <div class="about-modal-title">Buy Product</div>
            <form class="about-edit-form active" id="buyProductForm" action="" method="POST">
                @csrf
                <div class="about-edit-field">
                    <label>Selected Product</label>
                    <div id="buyProductSelectedLabel" class="rules-content" style="min-height:auto;">-</div>
                </div>

                <div class="about-edit-field">
                    <label for="buyQuantityInput">Quantity</label>
                    <input id="buyQuantityInput" type="number" name="quantity" min="1" max="100" value="1" required>
                </div>

                <div class="about-edit-field">
                    <label>Payment Method</label>
                    <div id="buyProductPendingNotice" class="reservation-status reservation-warning" style="display:none; margin-bottom: 10px;">
                        You have a pending pay at gym order. You can continue only with GCash.
                    </div>
                    <div class="payment-choice-grid">
                        <label class="payment-choice active" id="walkinChoice">
                            <input type="radio" name="payment_method" value="walk_in_payment" checked>
                            <i class="fas fa-hand-holding-dollar"></i> Pay in Walk-in
                        </label>
                        <label class="payment-choice" id="gcashChoice">
                            <input type="radio" name="payment_method" value="online_gcash">
                            <i class="fas fa-wallet"></i> Pay with GCash
                        </label>
                    </div>
                </div>

                <div id="gcashFields" hidden>
                    <div class="about-edit-field">
                        <label for="gcashNameInput">GCash Account Name</label>
                        <input id="gcashNameInput" type="text" name="gcash_name" maxlength="120" placeholder="Enter account name">
                    </div>
                    <div class="about-edit-field">
                        <label for="gcashNumberInput">GCash Number</label>
                        <input id="gcashNumberInput" type="text" name="gcash_number" maxlength="20" placeholder="09XXXXXXXXX">
                    </div>
                    <div class="about-edit-field">
                        <label for="gcashReferenceInput">Reference Number</label>
                        <input id="gcashReferenceInput" type="text" name="gcash_reference" maxlength="80" placeholder="Transaction reference">
                    </div>
                </div>

                <div class="about-edit-actions">
                    <button type="button" class="about-cancel-btn" id="buyProductCancelBtn">Cancel</button>
                    <button type="submit" class="about-save-btn">Buy</button>
                </div>
            </form>
        </div>
    </div>

    <div class="details-section" id="subscriptionsSection" style="display: none;">
        <div class="subscription-section-header">
            <div class="about-gym-label">Subscriptions</div>
        </div>
        @if($subscriptions->isEmpty())
            <div class="subscription-empty">No active subscriptions available.</div>
        @else
            <div class="subscriptions-grid">
                @foreach($subscriptions as $subscription)
                    @php
                        $expiresAt = $subscription->expires_at ? \Carbon\Carbon::parse($subscription->expires_at) : null;
                        $expiresInText = $expiresAt ? now()->diffForHumans($expiresAt) : 'No expiry set';
                        $subscriptionType = $subscription->subscription_type === 'gym_trainer' ? 'gym_trainer' : 'gym_access';
                        $subscriptionTypeLabel = $subscriptionType === 'gym_trainer' ? 'Gym Trainer' : 'Gym Access';
                        $subscriptionIconClass = $subscriptionType === 'gym_trainer' ? 'fas fa-user-tie' : 'fas fa-crown';
                    @endphp
                    <article class="subscription-card {{ $subscriptionType === 'gym_trainer' ? 'is-trainer' : 'is-access' }}">
                        <div class="subscription-icon-box"><i class="{{ $subscriptionIconClass }}"></i></div>
                        <div class="subscription-card-body">
                            <div class="subscription-duration">Type: {{ $subscriptionTypeLabel }}</div>
                            <div class="subscription-duration">Duration: {{ $subscription->duration }}</div>
                            <div class="subscription-expire">Expires in: {{ $expiresInText }}</div>
                            <div class="subscription-footer">
                                <div class="subscription-price">₱{{ number_format((float) $subscription->price, 2) }} pesos</div>
                                <button
                                    type="button"
                                    class="buy-product-btn open-subscription-buy-modal-btn"
                                    data-action="{{ route('users.gym.subscriptions.buy', ['business' => $business, 'subscription' => $subscription]) }}"
                                    data-subscription-name="{{ e($subscription->duration) }}"
                                    data-subscription-price="{{ number_format((float) $subscription->price, 2) }}"
                                >
                                    <i class="fas fa-wallet"></i> Buy
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <div class="about-modal-overlay" id="reservePaymentModalOverlay">
        <div class="about-modal">
            <div class="about-modal-title">Reserve Gym Slot</div>
            <form class="about-edit-form active" id="reservePaymentForm" action="{{ route('users.gym.reserve', $business) }}" method="POST">
                @csrf
                <div class="reservation-payment-note">
                    Reservation payment is through GCash.
                </div>

                <div class="about-edit-field">
                    <label for="reserveGcashNameInput">GCash Account Name</label>
                    <input id="reserveGcashNameInput" type="text" name="gcash_name" maxlength="120" placeholder="Enter account name" required>
                </div>
                <div class="about-edit-field">
                    <label for="reserveGcashNumberInput">GCash Number</label>
                    <input id="reserveGcashNumberInput" type="text" name="gcash_number" maxlength="20" placeholder="09XXXXXXXXX" required>
                </div>
                <div class="about-edit-field">
                    <label for="reserveGcashReferenceInput">Reference Number</label>
                    <input id="reserveGcashReferenceInput" type="text" name="gcash_reference" maxlength="80" placeholder="Transaction reference" required>
                </div>

                <div class="about-edit-actions">
                    <button type="button" class="about-cancel-btn" id="reservePaymentCancelBtn">Cancel</button>
                    <button type="submit" class="about-save-btn">Pay with GCash & Reserve</button>
                </div>
            </form>
        </div>
    </div>

    <div class="about-modal-overlay" id="cancelReservationModalOverlay">
        <div class="about-modal">
            <div class="about-modal-title">Cancel Reservation</div>
            <div class="about-edit-form active">
                <div class="reservation-payment-note" style="margin-bottom: 16px;">
                    Are you sure you want to proceed? No refunds will be issued for paid reservations, but you can reserve again if you change your mind.
                </div>
                <div class="about-edit-actions">
                    <button type="button" class="about-cancel-btn" id="cancelReservationNoBtn">No</button>
                    <form method="POST" action="{{ route('users.gym.reserve.cancel', $business) }}" id="cancelReservationForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="about-save-btn">Yes, Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="about-modal-overlay" id="buySubscriptionModalOverlay">
    <div class="about-modal">
        <div class="about-modal-title">Buy Subscription</div>
        <form class="about-edit-form active" id="buySubscriptionForm" action="" method="POST">
            @csrf
            <div class="about-edit-field">
                <label>Selected Subscription</label>
                <div id="buySubscriptionSelectedLabel" class="rules-content" style="min-height:auto;">-</div>
            </div>

            <div class="about-edit-field">
                <label>Payment Method</label>
                <div class="payment-choice-grid">
                    <div class="payment-choice active" style="cursor: default;">
                        <i class="fas fa-wallet"></i> Pay with GCash
                    </div>
                </div>
            </div>

            <div class="about-edit-field">
                <label for="subscriptionGcashNameInput">GCash Account Name</label>
                <input id="subscriptionGcashNameInput" type="text" name="gcash_name" maxlength="120" placeholder="Enter account name" required>
            </div>
            <div class="about-edit-field">
                <label for="subscriptionGcashNumberInput">GCash Number</label>
                <input id="subscriptionGcashNumberInput" type="text" name="gcash_number" maxlength="20" placeholder="09XXXXXXXXX" required>
            </div>
            <div class="about-edit-field">
                <label for="subscriptionGcashReferenceInput">Reference Number</label>
                <input id="subscriptionGcashReferenceInput" type="text" name="gcash_reference" maxlength="80" placeholder="Transaction reference" required>
            </div>

            <div class="about-edit-actions">
                <button type="button" class="about-cancel-btn" id="buySubscriptionCancelBtn">Cancel</button>
                <button type="submit" class="about-save-btn">Buy</button>
            </div>
        </form>
    </div>
</div>
<script>
    const hasPendingStorePayment = @json($hasPendingStorePayment ?? false);
    const hasSubscriptionAccess = @json($hasSubscriptionAccess ?? false);
    const homeNavBtn = document.getElementById('homeNavBtn');
    const reservationNavBtn = document.getElementById('reservationNavBtn');
    const storeNavBtn = document.getElementById('storeNavBtn');
    const subscriptionsNavBtn = document.getElementById('subscriptionsNavBtn');
    const aboutGymSection = document.getElementById('aboutGymSection');
    const rulesSection = document.getElementById('rulesSection');
    const reservationSection = document.getElementById('reservationSection');
    const storeSection = document.getElementById('storeSection');
    const subscriptionsSection = document.getElementById('subscriptionsSection');
    const buyProductPendingNotice = document.getElementById('buyProductPendingNotice');
    const openBuyModalBtns = document.querySelectorAll('.open-buy-modal-btn');
    const buyProductModalOverlay = document.getElementById('buyProductModalOverlay');
    const buyProductForm = document.getElementById('buyProductForm');
    const buyProductSelectedLabel = document.getElementById('buyProductSelectedLabel');
    const buyProductCancelBtn = document.getElementById('buyProductCancelBtn');
    const openSubscriptionBuyModalBtns = document.querySelectorAll('.open-subscription-buy-modal-btn');
    const buySubscriptionModalOverlay = document.getElementById('buySubscriptionModalOverlay');
    const buySubscriptionForm = document.getElementById('buySubscriptionForm');
    const buySubscriptionSelectedLabel = document.getElementById('buySubscriptionSelectedLabel');
    const buySubscriptionCancelBtn = document.getElementById('buySubscriptionCancelBtn');
    const subscriptionGcashNameInput = document.getElementById('subscriptionGcashNameInput');
    const subscriptionGcashNumberInput = document.getElementById('subscriptionGcashNumberInput');
    const subscriptionGcashReferenceInput = document.getElementById('subscriptionGcashReferenceInput');
    const walkinChoice = document.getElementById('walkinChoice');
    const gcashChoice = document.getElementById('gcashChoice');
    const gcashFields = document.getElementById('gcashFields');
    const gcashNameInput = document.getElementById('gcashNameInput');
    const gcashNumberInput = document.getElementById('gcashNumberInput');
    const gcashReferenceInput = document.getElementById('gcashReferenceInput');
    const openReservePaymentModalBtn = document.getElementById('openReservePaymentModalBtn');
    const reservePaymentModalOverlay = document.getElementById('reservePaymentModalOverlay');
    const reservePaymentForm = document.getElementById('reservePaymentForm');
    const reservePaymentCancelBtn = document.getElementById('reservePaymentCancelBtn');
    const openCancelReservationModalBtn = document.getElementById('openCancelReservationModalBtn');
    const cancelReservationModalOverlay = document.getElementById('cancelReservationModalOverlay');
    const cancelReservationNoBtn = document.getElementById('cancelReservationNoBtn');

    const allNavBtns = [homeNavBtn, reservationNavBtn, storeNavBtn, subscriptionsNavBtn];

    function removeActiveNav() {
        allNavBtns.forEach((btn) => {
            if (btn) btn.classList.remove('active');
        });
    }

    function showHome() {
        removeActiveNav();
        if (homeNavBtn) homeNavBtn.classList.add('active');
        if (aboutGymSection) aboutGymSection.style.display = 'block';
        if (rulesSection) rulesSection.style.display = 'block';
        if (reservationSection) reservationSection.style.display = 'none';
        if (storeSection) storeSection.style.display = 'none';
        if (subscriptionsSection) subscriptionsSection.style.display = 'none';
    }

    function showReservation() {
        removeActiveNav();
        if (reservationNavBtn) reservationNavBtn.classList.add('active');
        if (aboutGymSection) aboutGymSection.style.display = 'none';
        if (rulesSection) rulesSection.style.display = 'none';
        if (reservationSection) reservationSection.style.display = 'block';
        if (storeSection) storeSection.style.display = 'none';
        if (subscriptionsSection) subscriptionsSection.style.display = 'none';
    }

    function showStore() {
        removeActiveNav();
        if (storeNavBtn) storeNavBtn.classList.add('active');
        if (aboutGymSection) aboutGymSection.style.display = 'none';
        if (rulesSection) rulesSection.style.display = 'none';
        if (reservationSection) reservationSection.style.display = 'none';
        if (storeSection) storeSection.style.display = 'block';
        if (subscriptionsSection) subscriptionsSection.style.display = 'none';
    }

    function showSubscriptions() {
        removeActiveNav();
        if (subscriptionsNavBtn) subscriptionsNavBtn.classList.add('active');
        if (aboutGymSection) aboutGymSection.style.display = 'none';
        if (rulesSection) rulesSection.style.display = 'none';
        if (reservationSection) reservationSection.style.display = 'none';
        if (storeSection) storeSection.style.display = 'none';
        if (subscriptionsSection) subscriptionsSection.style.display = 'block';
    }

    if (homeNavBtn) homeNavBtn.addEventListener('click', showHome);
    if (reservationNavBtn) reservationNavBtn.addEventListener('click', showReservation);
    if (storeNavBtn) storeNavBtn.addEventListener('click', showStore);
    if (subscriptionsNavBtn) subscriptionsNavBtn.addEventListener('click', showSubscriptions);

    function updatePaymentFormState() {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        const isGcash = selected && selected.value === 'online_gcash';

        if (walkinChoice) walkinChoice.classList.toggle('active', !isGcash);
        if (gcashChoice) gcashChoice.classList.toggle('active', !!isGcash);

        if (gcashFields) {
            gcashFields.hidden = !isGcash;
        }

        if (gcashNameInput) gcashNameInput.required = !!isGcash;
        if (gcashNumberInput) gcashNumberInput.required = !!isGcash;
        if (gcashReferenceInput) gcashReferenceInput.required = !!isGcash;
    }

    function applyPendingStorePaymentRestriction() {
        if (!buyProductForm) {
            return;
        }

        const walkInRadio = buyProductForm.querySelector('input[name="payment_method"][value="walk_in_payment"]');
        const gcashRadio = buyProductForm.querySelector('input[name="payment_method"][value="online_gcash"]');

        if (hasPendingStorePayment) {
            if (buyProductPendingNotice) {
                buyProductPendingNotice.style.display = 'block';
            }
            if (walkInRadio) {
                walkInRadio.checked = false;
                walkInRadio.disabled = true;
            }
            if (gcashRadio) {
                gcashRadio.checked = true;
            }
            if (walkinChoice) {
                walkinChoice.classList.add('is-disabled');
            }
        } else {
            if (buyProductPendingNotice) {
                buyProductPendingNotice.style.display = 'none';
            }
            if (walkInRadio) {
                walkInRadio.disabled = false;
                walkInRadio.checked = true;
            }
            if (walkinChoice) {
                walkinChoice.classList.remove('is-disabled');
            }
        }

        updatePaymentFormState();
    }

    function closeBuyModal() {
        if (!buyProductModalOverlay || !buyProductForm) {
            return;
        }

        buyProductModalOverlay.classList.remove('active');
        buyProductForm.reset();
        applyPendingStorePaymentRestriction();
    }

    function closeSubscriptionBuyModal() {
        if (!buySubscriptionModalOverlay || !buySubscriptionForm) {
            return;
        }

        buySubscriptionModalOverlay.classList.remove('active');
        buySubscriptionForm.reset();
    }

    function closeReservePaymentModal() {
        if (!reservePaymentModalOverlay || !reservePaymentForm) {
            return;
        }

        reservePaymentModalOverlay.classList.remove('active');
        reservePaymentForm.reset();
    }

    function closeCancelReservationModal() {
        if (!cancelReservationModalOverlay) {
            return;
        }

        cancelReservationModalOverlay.classList.remove('active');
    }

    if (openBuyModalBtns.length && buyProductModalOverlay && buyProductForm) {
        openBuyModalBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const action = btn.getAttribute('data-action') || '';
                const productName = btn.getAttribute('data-product-name') || 'Product';
                const productPrice = btn.getAttribute('data-product-price') || '0.00';

                buyProductForm.action = action;
                if (buyProductSelectedLabel) {
                    buyProductSelectedLabel.textContent = `${productName} - P${productPrice}`;
                }

                buyProductModalOverlay.classList.add('active');
                applyPendingStorePaymentRestriction();
            });
        });

        if (buyProductCancelBtn) {
            buyProductCancelBtn.addEventListener('click', closeBuyModal);
        }

        buyProductModalOverlay.addEventListener('click', (event) => {
            if (event.target === buyProductModalOverlay) {
                closeBuyModal();
            }
        });
    }

    if (openSubscriptionBuyModalBtns.length && buySubscriptionModalOverlay && buySubscriptionForm) {
        openSubscriptionBuyModalBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const action = btn.getAttribute('data-action') || '';
                const subscriptionName = btn.getAttribute('data-subscription-name') || 'Subscription';
                const subscriptionPrice = btn.getAttribute('data-subscription-price') || '0.00';

                buySubscriptionForm.action = action;
                if (buySubscriptionSelectedLabel) {
                    buySubscriptionSelectedLabel.textContent = `${subscriptionName} - P${subscriptionPrice}`;
                }

                buySubscriptionModalOverlay.classList.add('active');
            });
        });

        if (buySubscriptionCancelBtn) {
            buySubscriptionCancelBtn.addEventListener('click', closeSubscriptionBuyModal);
        }

        buySubscriptionModalOverlay.addEventListener('click', (event) => {
            if (event.target === buySubscriptionModalOverlay) {
                closeSubscriptionBuyModal();
            }
        });
    }

    if (!hasSubscriptionAccess && openReservePaymentModalBtn && reservePaymentModalOverlay) {
        openReservePaymentModalBtn.addEventListener('click', () => {
            reservePaymentModalOverlay.classList.add('active');
        });

        if (reservePaymentCancelBtn) {
            reservePaymentCancelBtn.addEventListener('click', closeReservePaymentModal);
        }

        reservePaymentModalOverlay.addEventListener('click', (event) => {
            if (event.target === reservePaymentModalOverlay) {
                closeReservePaymentModal();
            }
        });
    }

    if (openCancelReservationModalBtn && cancelReservationModalOverlay) {
        openCancelReservationModalBtn.addEventListener('click', () => {
            cancelReservationModalOverlay.classList.add('active');
        });

        if (cancelReservationNoBtn) {
            cancelReservationNoBtn.addEventListener('click', closeCancelReservationModal);
        }

        cancelReservationModalOverlay.addEventListener('click', (event) => {
            if (event.target === cancelReservationModalOverlay) {
                closeCancelReservationModal();
            }
        });
    }

    document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
        input.addEventListener('change', updatePaymentFormState);
    });

    applyPendingStorePaymentRestriction();
</script>
</body>
</html>
