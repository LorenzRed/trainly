@php
  $coverImageUrl = $business->cover_path
    ? route('admin.business.media', ['business' => $business, 'type' => 'cover'])
    : null;

  $logoImageUrl = $business->logo_path
    ? route('admin.business.media', ['business' => $business, 'type' => 'logo'])
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

  $businessBio = old('notes', $notesForDisplay ?: 'Add your gym bio in Edit About');

  $locationDisplay = $business->user->location ?: 'Set location in admin profile';
  $openingTimeDisplay = $business->opening_time ? \Carbon\Carbon::createFromFormat('H:i:s', $business->opening_time)->format('h:i A') : null;
  $closingTimeDisplay = $business->closing_time ? \Carbon\Carbon::createFromFormat('H:i:s', $business->closing_time)->format('h:i A') : null;
  $openingHoursDisplay = ($openingTimeDisplay && $closingTimeDisplay)
    ? 'Mon-Sun: ' . $openingTimeDisplay . ' - ' . $closingTimeDisplay
    : 'Set opening and closing time';
  $membershipDisplay = $business->capacity_limit
    ? 'Up to ' . $business->capacity_limit . ' members'
    : 'Set capacity limit';
  $reservations = $business->reservations ?? collect();
  $acceptedReservationsCount = $reservations->whereNotNull('accepted_at')->count();
  $capacityMaxValue = (int) ($business->capacity_limit ?? 0);
  $capacityCurrentValue = (int) $acceptedReservationsCount;
  $capacityStatusDisplay = $business->capacity_limit
    ? $capacityCurrentValue . ' / ' . $business->capacity_limit
    : $capacityCurrentValue . ' / maximum capacity set by users';
  $entranceFeeDisplay = $business->entrance_fee !== null
    ? '₱'. number_format((float) $business->entrance_fee) . ' pesos'
    : 'Set entrance fee';
  $monthlyAccessDisplay = $business->monthly_access !== null
    ? '₱'. number_format((float) $business->monthly_access) . ' pesos'
    : 'Set monthly access';
  $rulesDisplay = $business->rules ?: 'Add your gym rules';
  $hasClosedMarker = is_string($business->notes) && str_contains($business->notes, $manualClosedMarker);
  $gymStatusValue = $hasClosedMarker ? 'closed' : strtolower(trim((string) ($business->status ?? '')));
  if (!in_array($gymStatusValue, ['open', 'closed'], true)) {
    $gymStatusValue = 'open';
  }
  $gymStatusLabel = $gymStatusValue === 'closed' ? 'Closed' : 'Open';
  $storeProducts = $business->products ?? collect();
  $subscriptions = $business->subscriptions ?? collect();
  $memberHistories = $memberHistories ?? collect();
  $storeOrders = $storeOrders ?? collect();
  $extractStock = static function (?string $description): int {
    $pattern = '/\[\[STOCK:(\d+)\]\]/';
    if (!is_string($description) || !preg_match($pattern, $description, $matches)) {
      return 0;
    }

    return max(0, (int) ($matches[1] ?? 0));
  };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>GYM PROFILE</title>
  <!-- Font Awesome 6 (free icons) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- Google Fonts: modern sans -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
  @vite(['resources/css/business-settings.css'])
  <style>
    #reservationsSection .walkin-actions {
      margin-left: auto;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      justify-content: flex-end;
      background: linear-gradient(180deg, rgba(19, 33, 23, 0.88) 0%, rgba(15, 27, 19, 0.96) 100%);
      border: 1px solid #1f2b22;
      border-radius: 16px;
      padding: 8px 10px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    #reservationsSection .walkin-name-field {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border: 1px solid #2b3a30;
      background: #0f1b13;
      border-radius: 14px;
      padding: 0 14px;
      min-height: 44px;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    #reservationsSection .walkin-name-field i {
      width: 22px;
      height: 22px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(251, 191, 36, 0.15);
      color: #fbbf24;
      font-size: 0.72rem;
    }
    #reservationsSection .walkin-name-input {
      border: none;
      background: transparent;
      color: #f3f4f6;
      min-width: 240px;
      padding: 8px 0;
      font-size: 0.82rem;
      font-weight: 600;
      letter-spacing: 0.2px;
      outline: none;
    }
    #reservationsSection .walkin-name-input::placeholder {
      color: #9ca3af;
      opacity: 1;
    }
    #reservationsSection .walkin-name-field:focus-within {
      border-color: #fbbf24;
      box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.24), 0 0 0 6px rgba(251, 191, 36, 0.08);
    }
    #reservationsSection .walkin-main-btn {
      border: none;
      border-radius: 14px;
      min-height: 44px;
      padding: 0 18px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.8rem;
      font-weight: 700;
      background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
      color: #07130b;
      box-shadow: 0 8px 18px rgba(251, 191, 36, 0.28);
      cursor: pointer;
    }
    #reservationsSection .walkin-main-btn:hover {
      background: linear-gradient(135deg, #fcd34d 0%, #f59e0b 100%);
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
    #storeSection .product-edit-btn {
      border: none;
      border-radius: 999px;
      padding: 6px 10px;
      font-size: 0.72rem;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      background: #22c55e;
      color: #052e16;
      transition: 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    #storeSection .product-edit-btn:hover {
      background: #16a34a;
      transform: translateY(-1px);
      box-shadow: 0 6px 14px rgba(34, 197, 94, 0.28);
    }
    #ordersSection .reservation-item,
    #memberHistoriesSection .reservation-item {
      position: relative;
    }
    #ordersSection .card-corner-actions,
    #memberHistoriesSection .card-corner-actions {
      position: absolute;
      top: 14px;
      right: 14px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      z-index: 2;
    }
    #ordersSection .card-corner-actions {
      top: auto;
      bottom: 14px;
    }
    #ordersSection .order-item {
      padding-bottom: 58px;
    }
    #ordersSection .card-icon-btn,
    #memberHistoriesSection .card-icon-btn {
      width: 34px;
      height: 34px;
      border-radius: 999px;
      border: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 0.8rem;
      transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    #ordersSection .card-icon-btn:hover,
    #memberHistoriesSection .card-icon-btn:hover {
      transform: translateY(-1px);
    }
    #ordersSection .card-icon-btn.complete-btn {
      background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
      color: #052e16;
      box-shadow: 0 8px 16px rgba(16, 185, 129, 0.24);
    }
    #memberHistoriesSection .card-icon-btn.delete-btn {
      background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
      color: #fff1f2;
      box-shadow: 0 8px 16px rgba(239, 68, 68, 0.22);
    }
    #subscriptionsSection .subscription-card.is-trainer .subscription-icon-box {
      background: #0b0b0b;
      border: 1px solid #1f2937;
    }
    #subscriptionsSection .subscription-card.is-trainer .subscription-icon {
      color: #facc15;
    }
    .subscription-type-select-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }
    .subscription-type-select-wrap i {
      position: absolute;
      left: 12px;
      color: #fbbf24;
      font-size: 0.82rem;
      pointer-events: none;
      z-index: 1;
    }
    #subscriptionTypeInput {
      width: 100%;
      border: 1px solid #1f2b22;
      background: linear-gradient(180deg, #132117 0%, #0f1b13 100%);
      color: #e5e7eb;
      border-radius: 12px;
      min-height: 44px;
      padding: 10px 38px 10px 36px;
      font-family: inherit;
      font-size: 0.84rem;
      font-weight: 700;
      letter-spacing: 0.2px;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    #subscriptionTypeInput:focus {
      outline: none;
      border-color: #fbbf24;
      box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.25);
    }
    .subscription-type-select-wrap::after {
      content: "\f078";
      font-family: "Font Awesome 6 Free";
      font-weight: 900;
      position: absolute;
      right: 12px;
      color: #fde68a;
      pointer-events: none;
      font-size: 0.72rem;
    }
    #subscriptionTypeInput option {
      background: #111827;
      color: #e5e7eb;
    }
    @media (max-width: 760px) {
      #reservationsSection .walkin-actions {
        width: 100%;
        margin-left: 0;
        justify-content: stretch;
      }
      #reservationsSection .walkin-name-field {
        width: 100%;
      }
      #reservationsSection .walkin-name-input {
        min-width: 0;
        width: 100%;
      }
      #reservationsSection .walkin-main-btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>
<body>

<div class="profile-container" id="gymProfileApp">
  <div class="top-nav">
    <a href="{{ route('admin.main') }}" class="back-home-btn" aria-label="Back to Home" title="Back to Home"><i class="fas fa-arrow-left"></i></a>
  </div>

  <form id="coverUploadForm" action="{{ route('admin.business.settings.update', $business) }}" method="post" enctype="multipart/form-data" style="display:none;">
    @csrf
    @method('PUT')
    <input type="file" id="coverInput" name="cover_image" accept="image/*">
  </form>

  <form id="logoUploadForm" action="{{ route('admin.business.settings.update', $business) }}" method="post" enctype="multipart/form-data" style="display:none;">
    @csrf
    @method('PUT')
    <input type="file" id="logoInput" name="logo_image" accept="image/*">
  </form>

  <!-- COVER / BACKGROUND area -->
  <div class="cover-area">
    @if($coverImageUrl)
      <img class="cover-image" id="coverImg" src="{{ $coverImageUrl }}" alt="cover">
    @else
      <div class="cover-empty" id="coverImg">
        <i class="fas fa-image" aria-hidden="true"></i>
        <span>Upload cover image</span>
      </div>
    @endif
    <div class="cover-overlay">
      <button type="button" class="edit-cover-btn" id="changeCoverBtn"><i class="fas fa-camera"></i> Change cover</button>
    </div>
  </div>

  <!-- PROFILE PIC + USER INFO -->
  <div class="profile-pic-section">
    <div class="avatar-wrapper">
      @if($logoImageUrl)
        <img class="profile-img" id="profileImg" src="{{ $logoImageUrl }}" alt="profile">
      @else
        <div class="profile-img-empty" id="profileImg">
          <i class="fas fa-user-circle" aria-hidden="true"></i>
          <span>Upload logo</span>
        </div>
      @endif
      <div class="edit-avatar-icon" id="changeAvatarBtn" role="button" tabindex="0">
        <i class="fas fa-camera"></i>
      </div>
    </div>
    <div class="profile-info">
      <div class="profile-name">{{ $businessDisplayName }}</div>
      <div class="profile-bio">{{ $businessBio }}</div>
      <div class="gym-open-close" aria-label="Gym status buttons">
        <div
          class="gym-capacity-indicator"
          id="gymCapacityIndicator"
          data-current="{{ $capacityCurrentValue }}"
          data-max="{{ $capacityMaxValue }}"
        ><i class="fas fa-users"></i> <span id="gymCapacityText">{{ $capacityStatusDisplay }}</span></div>
        <form action="{{ route('admin.business.status.toggle', $business) }}" method="post" style="display:inline-flex;">
          @csrf
          @method('PATCH')
            <button type="submit" class="gym-status-btn {{ $gymStatusValue === 'closed' ? 'is-closed' : 'is-open' }}" id="gymToggleBtn">
              <i class="fas {{ $gymStatusValue === 'closed' ? 'fa-door-closed' : 'fa-door-open' }}"></i> {{ $gymStatusLabel }}
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- TOP NAV -->
  <div class="details-top-nav" aria-label="About section top navigation">
    <button type="button" class="details-top-nav-link active" id="homeNavBtn"><i class="fas fa-house"></i> Home</button>
    <button type="button" class="details-top-nav-link" id="aboutReservationNavBtn"><i class="fas fa-calendar-check"></i> Reservation</button>
    <button type="button" class="details-top-nav-link" id="aboutStoreNavBtn"><i class="fas fa-store"></i> Store</button>
    <button type="button" class="details-top-nav-link" id="aboutSubscriptionNavBtn"><i class="fas fa-crown"></i> Subscriptions</button>
    <button type="button" class="details-top-nav-link" id="aboutOrdersNavBtn"><i class="fas fa-receipt"></i> Transactions</button>
    <button type="button" class="details-top-nav-link" id="memberHistoryNavBtn"><i class="fas fa-history"></i> History</button>
    <a href="{{ route('admin.business') }}" class="details-top-nav-link" id="locationNavBtn"><i class="fas fa-map-location-dot"></i> Change Location</a>
  </div>

  <!-- BOTTOM DETAILS SECTION -->
  <div class="details-section" id="aboutGymSection">
    <div class="about-header">
      <div class="about-gym-label">About your gym</div>
    </div>
    <div class="details-title">
      <i class="fas fa-dumbbell"></i> GYM DETAILS
    </div>
    <div class="details-grid" id="detailsGrid">
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

    <div class="about-bottom-actions">
      <button type="button" class="about-edit-btn" id="aboutEditToggleBtn" onclick="document.getElementById('aboutModalOverlay').classList.add('active')"><i class="fas fa-pen"></i> Edit About</button>
    </div>

    <div class="about-modal-overlay" id="aboutModalOverlay">
      <div class="about-modal">
        <div class="about-modal-title">Edit About Your Gym</div>
        <form class="about-edit-form active" id="aboutEditForm" action="{{ route('admin.business.settings.update', $business) }}" method="post">
          @csrf
          @method('PUT')
          <div class="about-edit-row">
            <div class="about-edit-field">
              <label for="facebookPageInput">Facebook Page Link</label>
              <input id="facebookPageInput" type="url" name="facebook_page_link" value="{{ old('facebook_page_link', $facebookPageLinkForDisplay) }}" placeholder="https://facebook.com/your-gym-page">
            </div>
            <div class="about-edit-field">
              <label for="locationInput">Location</label>
              <input id="locationInput" type="text" name="location" value="{{ old('location', $business->user->location) }}" placeholder="Enter gym location">
            </div>
            <div class="about-edit-field">
              <label for="openingTimeInput">Opening Time</label>
              <input id="openingTimeInput" type="time" name="opening_time" value="{{ old('opening_time', $openingTimeDisplay) }}">
            </div>
            <div class="about-edit-field">
              <label for="closingTimeInput">Closing Time</label>
              <input id="closingTimeInput" type="time" name="closing_time" value="{{ old('closing_time', $closingTimeDisplay) }}">
            </div>
            <div class="about-edit-field">
              <label for="capacityLimitInput">Gym Capacity</label>
              <input id="capacityLimitInput" type="number" name="capacity_limit" min="1" max="100000" value="{{ old('capacity_limit', $business->capacity_limit) }}" placeholder="e.g. 15">
            </div>
            <div class="about-edit-field">
              <label for="entranceFeeInput">Entrance Fee</label>
              <input id="entranceFeeInput" type="number" step="0.01" min="0" name="entrance_fee" value="{{ old('entrance_fee', $business->entrance_fee) }}" placeholder="e.g. 50 pesos">
            </div>
            <div class="about-edit-field">
              <label for="monthlyAccessInput">Monthly (30 days)</label>
              <input id="monthlyAccessInput" type="number" step="0.01" min="0" name="monthly_access" value="{{ old('monthly_access', $business->monthly_access) }}" placeholder="e.g. 750pesos">
            </div>
          </div>
          <div class="about-edit-field">
            <label for="notesInput">Bio</label>
            <textarea id="notesInput" name="notes" maxlength="25" placeholder="Add your gym bio">{{ old('notes', $notesForDisplay) }}</textarea>
          </div>
          <div class="about-edit-actions">
            <button type="button" class="about-cancel-btn" id="aboutCancelBtn" onclick="document.getElementById('aboutModalOverlay').classList.remove('active')">Cancel</button>
            <button type="submit" class="about-save-btn">Save Details</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="rules-section" id="rulesSection">
    <div class="rules-section-header">
      <div class="rules-title"><i class="fas fa-file-contract"></i> Gym Rules</div>
    </div>
    <div class="rules-content clickable" id="rulesContent" title="Click to edit rules">{{ $rulesDisplay }}</div>
  </div>

  <!-- RESERVATIONS SECTION -->
  <div class="details-section" id="reservationsSection" style="display: none;">
    <div class="about-header">
      <div class="about-gym-label">Member Reservations</div>
      <form action="{{ route('admin.business.reservations.walkin', ['business' => $business]) }}" method="post" class="walkin-actions">
        @csrf
        <label for="walkInNameInput" class="walkin-name-field" aria-label="Walk-in customer name">
          <i class="fas fa-id-card" aria-hidden="true"></i>
          <input
            id="walkInNameInput"
            type="text"
            name="walkin_name"
            class="walkin-name-input"
            placeholder="Enter fullname of walk-in"
            value="{{ old('walkin_name') }}"
            required
          >
        </label>
        <button type="submit" class="walkin-add-btn walkin-main-btn" id="addWalkInBtn">
          <i class="fas fa-user-plus"></i> Add Walk-in
        </button>
      </form>
    </div>
    <div class="reservations-list" id="reservationsList">
      @if($reservations->isEmpty())
        <div class="subscription-empty">No members have reserved yet.</div>
      @else
        @foreach($reservations as $reservation)
          @php
            $reservedAt = $reservation->reserved_at ?: $reservation->created_at;
          @endphp
          <div class="reservation-item">
            <div class="reservation-header">
              <div class="reservation-name">{{ $reservation->user?->name ?: 'Unknown User' }}</div>
              <div class="reservation-time">{{ $reservedAt ? $reservedAt->diffForHumans() : 'Recently' }}</div>
            </div>
            <div class="reservation-detail" style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
              <span><i class="fas fa-envelope"></i> {{ $reservation->user?->email ?: 'No email provided' }}</span>
              <span style="font-size:0.78rem; font-weight:700; color: #86efac;">
                Accepted
              </span>
            </div>
            <div style="display:flex; align-items:center; gap:8px; margin-top:10px;">
              <form action="{{ route('admin.business.reservations.remove', ['business' => $business, 'reservation' => $reservation]) }}" method="post">
                @csrf
                @method('DELETE')
                <button type="submit" class="walkin-remove-btn">
                  <i class="fas fa-user-slash"></i> Remove
                </button>
              </form>
            </div>
          </div>
        @endforeach
      @endif
    </div>
  </div>

  <!-- MEMBER HISTORY SECTION -->
  <div class="details-section" id="memberHistoriesSection" style="display: none;">
    <div class="about-header">
      <div class="about-gym-label">Member History</div>
    </div>
    <div class="reservations-list" id="memberHistoryList">
      @if($memberHistories->isEmpty())
        <div class="subscription-empty">No member history yet.</div>
      @else
        @foreach($memberHistories as $history)
          @php
            $paidMethod = $history->is_walk_in ? 'Paid (Walk-in)' : 'Paid (GCash)';
          @endphp
          <div class="reservation-item history-item">
            <div class="reservation-header">
              <div class="reservation-name">{{ $history->member_name ?: 'Unknown Member' }}</div>
              <div class="reservation-time">{{ $history->is_walk_in ? 'Walk-in' : 'Reserved Member' }}</div>
            </div>
            <div class="reservation-detail" style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
              <span><i class="fas fa-envelope"></i> {{ $history->member_email ?: 'No email provided' }}</span>
              <span style="font-size:0.78rem; font-weight:700; color: {{ $history->time_out ? '#fca5a5' : '#86efac' }};">
                {{ $history->time_out ? 'Completed' : 'Inside Gym' }}
              </span>
            </div>
            <div class="reservation-detail" style="margin-top:6px;">
              <span><i class="fas fa-money-bill-wave"></i> {{ $paidMethod }}</span>
            </div>
            <div class="reservation-detail" style="margin-top:8px; flex-direction:column; align-items:flex-start; gap:4px;">
              <span><i class="fas fa-right-to-bracket"></i> Time In: {{ $history->time_in ? $history->time_in->format('M d, Y h:i A') : 'N/A' }}</span>
              <span><i class="fas fa-right-from-bracket"></i> Time Out: {{ $history->time_out ? $history->time_out->format('M d, Y h:i A') : 'Still inside' }}</span>
              @if($history->time_out_reason)
                <span><i class="fas fa-note-sticky"></i> Note: {{ $history->time_out_reason }}</span>
              @endif
            </div>
          </div>
        @endforeach
      @endif
    </div>
  </div>

  <!-- STORE SECTION -->
  <div class="details-section" id="storeSection" style="display: none;">
    <div class="store-section-header">
      <div class="about-gym-label">Store Products</div>
      <button type="button" class="store-add-btn" id="openAddProductModalBtn" title="Add product" aria-label="Add product">
        <i class="fas fa-plus"></i>
      </button>
    </div>
    @if($storeProducts->isEmpty())
      <div class="store-empty" id="storeEmptyText">No products yet. Click + to add your first product.</div>
    @endif
    <div class="store-products-grid" id="storeProductsGrid">
      @foreach($storeProducts as $product)
        @php
          $productStock = $extractStock($product->description);
          $isSoldOut = $productStock <= 0;
        @endphp
        <article class="product-card {{ $isSoldOut ? 'sold' : '' }}">
          <div class="product-image-wrap">
            <img class="product-image" src="{{ route('admin.business.products.media', ['business' => $business, 'product' => $product]) }}" alt="{{ $product->name }}">
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
                class="product-edit-btn open-edit-product-modal-btn"
                data-update-action="{{ route('admin.business.products.update', ['business' => $business, 'product' => $product]) }}"
                data-product-name="{{ e($product->name) }}"
                data-product-price="{{ number_format((float) $product->price, 2, '.', '') }}"
                data-product-stock="{{ $productStock }}"
              >
                <i class="fas fa-pen"></i> Edit
              </button>
              <form action="{{ route('admin.business.products.toggle-sold', ['business' => $business, 'product' => $product]) }}" method="post">
                @csrf
                @method('PATCH')
                <button type="submit" class="product-sold-btn">{{ $isSoldOut ? 'Sold Out' : 'Mark Sold Out' }}</button>
              </form>
            </div>
          </div>
        </article>
      @endforeach
    </div>
  </div>

  <div class="about-modal-overlay" id="storeProductModalOverlay">
    <div class="about-modal">
      <div class="about-modal-title">Add Store Product</div>
      <form class="about-edit-form active" id="storeProductForm" action="{{ route('admin.business.products.store', $business) }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="about-edit-field">
          <label for="productNameInput">Product Name</label>
          <input id="productNameInput" type="text" name="name" maxlength="100" placeholder="e.g. Whey Protein 2kg" required>
        </div>
        <div class="about-edit-field">
          <label for="productImageInput">Image</label>
          <div class="product-modal-upload">
            <input id="productImageInput" type="file" name="image" accept="image/*" required>
            <img id="productImagePreview" class="product-image-preview" alt="Product preview">
          </div>
        </div>
        <div class="about-edit-field">
          <label for="productPriceInput">Price</label>
          <input id="productPriceInput" type="number" name="price" min="0" step="0.01" placeholder="e.g. 1499 pesos" required>
        </div>
        <div class="about-edit-field">
          <label for="productStockInput">Stock</label>
          <input id="productStockInput" type="number" name="stock" min="0" step="1" placeholder="e.g. 20" required>
        </div>
        <div class="about-edit-actions">
          <button type="button" class="about-cancel-btn" id="storeProductCancelBtn">Cancel</button>
          <button type="submit" class="about-save-btn">Post Product</button>
        </div>
      </form>
    </div>
  </div>

  <div class="about-modal-overlay" id="editProductModalOverlay">
    <div class="about-modal">
      <div class="about-modal-title">Edit Product</div>
      <form class="about-edit-form active" id="editProductForm" action="" method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="about-edit-field">
          <label for="editProductNameInput">Product Name</label>
          <input id="editProductNameInput" type="text" name="name" maxlength="100" required>
        </div>
        <div class="about-edit-field">
          <label for="editProductPriceInput">Price</label>
          <input id="editProductPriceInput" type="number" name="price" min="0" step="0.01" required>
        </div>
        <div class="about-edit-field">
          <label for="editProductStockInput">Stock</label>
          <input id="editProductStockInput" type="number" name="stock" min="0" step="1" required>
        </div>
        <div class="about-edit-field">
          <label for="editProductImageInput">Replace Image (optional)</label>
          <input id="editProductImageInput" type="file" name="image" accept="image/*">
        </div>
        <div class="about-edit-actions">
          <button type="button" class="about-cancel-btn" id="editProductCancelBtn">Cancel</button>
          <button type="submit" class="about-save-btn">Save Product</button>
        </div>
      </form>
    </div>
  </div>

  <!-- STORE ORDERS SECTION -->
  <div class="details-section" id="ordersSection" style="display: none;">
    <div class="about-header">
      <div class="about-gym-label">Transactions</div>
    </div>
    <div class="reservations-list" id="ordersList">
      @if($storeOrders->isEmpty())
        <div class="subscription-empty">No store orders yet.</div>
      @else
        @foreach($storeOrders as $order)
          <div class="reservation-item order-item">
            <div class="card-corner-actions">
              @if(($order->payment_method === 'walk_in_payment' || ($order->payment_method === 'online_gcash' && $order->subscription_id)) && $order->payment_status !== 'paid')
                <form action="{{ route('admin.business.orders.complete', ['business' => $business, 'order' => $order]) }}" method="post">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="card-icon-btn complete-btn" aria-label="Complete order" title="Complete order">
                    <i class="fas fa-check"></i>
                  </button>
                </form>
              @endif
            </div>
            <div class="reservation-header">
              <div class="reservation-name">
                {{ $order->product?->name ?: ($order->subscription?->duration ? 'Subscription: ' . $order->subscription->duration : 'Unknown Order') }}
              </div>
              <div class="reservation-time">{{ $order->ordered_at ? $order->ordered_at->diffForHumans() : 'Recently' }}</div>
            </div>
            <div class="reservation-detail" style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
              <span><i class="fas fa-user"></i> {{ $order->customer_name ?: 'Unknown Customer' }}</span>
              <span class="order-payment-badge {{ $order->payment_method === 'online_gcash' ? 'is-gcash' : 'is-walkin' }}">
                {{ $order->payment_method === 'online_gcash' ? 'Online (GCash)' : 'Walk-in Payment' }}
              </span>
            </div>
            <div class="reservation-detail" style="margin-top:8px; flex-direction:column; align-items:flex-start; gap:4px;">
              <span><i class="fas fa-cubes"></i> Quantity: {{ $order->quantity }}</span>
              <span><i class="fas fa-money-bill-wave"></i> Total: ₱{{ number_format((float) $order->total_price, 2) }}</span>
              <span><i class="fas fa-circle-check"></i> Status: {{ $order->payment_status === 'paid' ? 'Completed' : 'Pending' }}</span>
              @if($order->gcash_reference)
                <span><i class="fas fa-receipt"></i> Ref: {{ $order->gcash_reference }}</span>
              @endif
            </div>
          </div>
        @endforeach
      @endif
    </div>
  </div>

  <!-- SUBSCRIPTIONS SECTION -->
  <div class="details-section" id="subscriptionsSection" style="display: none;">
    <div class="subscription-section-header">
      <div class="about-gym-label">Subscriptions</div>
      <button type="button" class="subscription-add-btn" id="openAddSubscriptionModalBtn" title="Add subscription" aria-label="Add subscription">
        <i class="fas fa-plus"></i>
      </button>
    </div>
    <div class="subscription-empty" id="subscriptionEmptyText" style="{{ $subscriptions->isEmpty() ? '' : 'display:none;' }}">No subscriptions yet. Click + to add one.</div>
    <div class="subscriptions-grid" id="subscriptionsGrid">
      @foreach($subscriptions as $subscription)
        @php
          $expiresAt = $subscription->expires_at ? \Carbon\Carbon::parse($subscription->expires_at) : null;
          $expiresInText = $expiresAt ? now()->diffForHumans($expiresAt) : 'No expiry set';
          $subscriptionType = $subscription->subscription_type === 'gym_trainer' ? 'gym_trainer' : 'gym_access';
          $subscriptionTypeLabel = $subscriptionType === 'gym_trainer' ? 'Gym Trainer' : 'Gym Access';
          $subscriptionIconClass = $subscriptionType === 'gym_trainer' ? 'fas fa-user-tie' : 'fas fa-crown';
        @endphp
        <article class="subscription-card {{ $subscriptionType === 'gym_trainer' ? 'is-trainer' : 'is-access' }}" data-expires-at="{{ $expiresAt ? $expiresAt->format('Y-m-d\\TH:i:s') : '' }}">
          <div class="subscription-icon-box">
            <div class="subscription-icon"><i class="{{ $subscriptionIconClass }}"></i></div>
          </div>
          <div class="subscription-card-body">
            <div class="subscription-duration">Type: {{ $subscriptionTypeLabel }}</div>
            <div class="subscription-duration">Duration: {{ $subscription->duration }}</div>
            <div class="subscription-expire">Expires in: <span class="subscription-expire-value">{{ $expiresInText }}</span></div>
            <div class="subscription-footer">
              <div class="subscription-price">₱{{ number_format((float) $subscription->price, 2) }} pesos</div>
              <button
                type="button"
                class="subscription-expire-btn"
                data-subscription-id="{{ $subscription->id }}"
                id="setExpireBtn{{ $subscription->id }}"
              >Set Expire</button>
            </div>
          </div>
        </article>
      @endforeach
    </div>
  </div>

  <div class="about-modal-overlay" id="subscriptionModalOverlay">
    <div class="about-modal">
      <div class="about-modal-title">Add Subscription</div>
      <form class="about-edit-form active" id="subscriptionForm" action="{{ route('admin.business.subscriptions.store', $business) }}" method="post">
        @csrf
        <div class="about-edit-field">
          <label for="subscriptionDurationInput">Duration</label>
          <input id="subscriptionDurationInput" type="text" name="duration" maxlength="100" placeholder="e.g. 1 Month" required>
        </div>
        <div class="about-edit-field">
          <label for="subscriptionTypeInput">Subscription Type</label>
          <div class="subscription-type-select-wrap">
            <i class="fas fa-layer-group" aria-hidden="true"></i>
            <select id="subscriptionTypeInput" name="subscription_type" required>
              <option value="gym_access">Gym Access</option>
              <option value="gym_trainer">Gym Trainer</option>
            </select>
          </div>
        </div>
        <div class="about-edit-field">
          <label for="subscriptionPriceInput">Price</label>
          <input id="subscriptionPriceInput" type="number" name="price" min="0" step="0.01" placeholder="e.g. 799 pesos" required>
        </div>
        <div class="about-edit-field">
          <label for="subscriptionExpiresAtInput">Expires On</label>
          <input id="subscriptionExpiresAtInput" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}">
        </div>
        <div class="about-edit-actions">
          <button type="button" class="about-cancel-btn" id="subscriptionCancelBtn">Cancel</button>
          <button type="submit" class="about-save-btn">Add Subscription</button>
        </div>
      </form>
    </div>
  </div>

  <div class="about-modal-overlay" id="setSubscriptionExpiryModalOverlay">
    <div class="about-modal">
      <div class="about-modal-title">Set Subscription Expiry</div>
      <form class="about-edit-form active" id="setSubscriptionExpiryForm" action="" method="post">
        @csrf
        @method('PATCH')
        <div class="about-edit-field">
          <label for="setSubscriptionExpiresAtInput">Expires On</label>
          <input id="setSubscriptionExpiresAtInput" type="datetime-local" name="expires_at" required>
        </div>
        <div class="about-edit-actions">
          <button type="button" class="about-cancel-btn" id="setSubscriptionExpiryCancelBtn">Cancel</button>
          <button type="submit" class="about-save-btn">Save Expiry</button>
        </div>
      </form>
    </div>
  </div>

  <div class="about-modal-overlay" id="rulesModalOverlay">
    <div class="about-modal">
      <div class="about-modal-title">Edit Gym Rules</div>
      <form class="about-edit-form active" id="rulesEditForm" action="{{ route('admin.business.settings.update', $business) }}" method="post">
        @csrf
        @method('PUT')
        <div class="about-edit-field">
          <label for="rulesModalInput">Rules</label>
          <textarea id="rulesModalInput" name="rules" maxlength="2000" placeholder="Add your gym rules">{{ old('rules', $business->rules) }}</textarea>
        </div>
        <div class="about-edit-actions">
          <button type="button" class="about-cancel-btn" id="rulesCancelBtn">Cancel</button>
          <button type="submit" class="about-save-btn">Save Rules</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // ----- DOM elements -----
  const coverImg = document.getElementById('coverImg');
  const profileImg = document.getElementById('profileImg');
  const changeCoverBtn = document.getElementById('changeCoverBtn');
  const changeAvatarBtn = document.getElementById('changeAvatarBtn');
  const coverUploadForm = document.getElementById('coverUploadForm');
  const logoUploadForm = document.getElementById('logoUploadForm');
  const coverInput = document.getElementById('coverInput');
  const logoInput = document.getElementById('logoInput');
  const aboutEditToggleBtn = document.getElementById('aboutEditToggleBtn');
  const aboutEditForm = document.getElementById('aboutEditForm');
  const aboutCancelBtn = document.getElementById('aboutCancelBtn');
  const aboutModalOverlay = document.getElementById('aboutModalOverlay');
  const rulesContent = document.getElementById('rulesContent');
  const rulesModalOverlay = document.getElementById('rulesModalOverlay');
  const rulesCancelBtn = document.getElementById('rulesCancelBtn');
  const storeSection = document.getElementById('storeSection');
  const openAddProductModalBtn = document.getElementById('openAddProductModalBtn');
  const storeProductModalOverlay = document.getElementById('storeProductModalOverlay');
  const storeProductForm = document.getElementById('storeProductForm');
  const storeProductCancelBtn = document.getElementById('storeProductCancelBtn');
  const productNameInput = document.getElementById('productNameInput');
  const productPriceInput = document.getElementById('productPriceInput');
  const productStockInput = document.getElementById('productStockInput');
  const productImageInput = document.getElementById('productImageInput');
  const productImagePreview = document.getElementById('productImagePreview');
  const openEditProductModalBtns = document.querySelectorAll('.open-edit-product-modal-btn');
  const editProductModalOverlay = document.getElementById('editProductModalOverlay');
  const editProductForm = document.getElementById('editProductForm');
  const editProductNameInput = document.getElementById('editProductNameInput');
  const editProductPriceInput = document.getElementById('editProductPriceInput');
  const editProductStockInput = document.getElementById('editProductStockInput');
  const editProductImageInput = document.getElementById('editProductImageInput');
  const editProductCancelBtn = document.getElementById('editProductCancelBtn');
  const subscriptionsSection = document.getElementById('subscriptionsSection');
  const openAddSubscriptionModalBtn = document.getElementById('openAddSubscriptionModalBtn');
  const subscriptionModalOverlay = document.getElementById('subscriptionModalOverlay');
  const subscriptionCancelBtn = document.getElementById('subscriptionCancelBtn');
  const setSubscriptionExpiryModalOverlay = document.getElementById('setSubscriptionExpiryModalOverlay');
  const setSubscriptionExpiryForm = document.getElementById('setSubscriptionExpiryForm');
  const setSubscriptionExpiresAtInput = document.getElementById('setSubscriptionExpiresAtInput');
  const setSubscriptionExpiryCancelBtn = document.getElementById('setSubscriptionExpiryCancelBtn');
  const subscriptionsGrid = document.getElementById('subscriptionsGrid');
  const subscriptionEmptyText = document.getElementById('subscriptionEmptyText');
  const gymToggleBtn = document.getElementById('gymToggleBtn');
  const removeWalkInBtn = document.getElementById('removeWalkInBtn');
  const addWalkInBtn = document.getElementById('addWalkInBtn');
  const reservationsList = document.getElementById('reservationsList');
  const ordersSection = document.getElementById('ordersSection');
  const gymCapacityIndicator = document.getElementById('gymCapacityIndicator');
  const gymCapacityText = document.getElementById('gymCapacityText');
  const storeCheckbox = document.getElementById('storeCheckbox');
  const storeStatusText = document.getElementById('storeStatusText');
  const storePanel = document.getElementById('storePanel');
  const classOptions = document.querySelectorAll('.class-option');
  const reserveBtn = document.getElementById('reserveClassBtn');
  const reservationFeedback = document.getElementById('reservationFeedback');

  function setUploadFeedback(message, color) {
    if (!reservationFeedback) {
      return;
    }

    reservationFeedback.innerText = message;
    if (color) {
      reservationFeedback.style.color = color;
    }
  }

  // active class tracking
  let selectedClass = "HIIT Burn";   // default active class

  // ----- Helper: update store UI based on switch -----
  function updateStoreUI() {
    if (!storeCheckbox || !storeStatusText || !storePanel) {
      return;
    }

    const isStoreOpen = storeCheckbox.checked;
    if (isStoreOpen) {
      storeStatusText.innerText = "Open";
      storeStatusText.style.color = "#86e1a0";
      storePanel.classList.add('active-store');
    } else {
      storeStatusText.innerText = "Closed";
      storeStatusText.style.color = "#f28b82";
      storePanel.classList.remove('active-store');
    }
  }

  // ----- Change Profile Icon (avatar) -----
  function changeProfileIcon() {
    // create an array of cool gym/athlete images from randomuser & unsplash mix
    const avatarOptions = [
      'https://randomuser.me/api/portraits/men/32.jpg',
      'https://randomuser.me/api/portraits/women/68.jpg',
      'https://randomuser.me/api/portraits/men/45.jpg',
      'https://randomuser.me/api/portraits/men/91.jpg',
      'https://randomuser.me/api/portraits/women/33.jpg',
      'https://randomuser.me/api/portraits/lego/1.jpg',
      'https://randomuser.me/api/portraits/men/22.jpg',
      'https://images.unsplash.com/photo-1594381898411-846e7d193883?w=150&h=150&fit=crop' // athlete style
    ];
    let currentSrc = profileImg.src;
    let newSrc = avatarOptions[Math.floor(Math.random() * avatarOptions.length)];
    // avoid same image sometimes
    if (newSrc === currentSrc && avatarOptions.length > 1) {
      newSrc = avatarOptions[(avatarOptions.indexOf(currentSrc) + 1) % avatarOptions.length];
    }
    profileImg.src = newSrc;
    // optional: show subtle feedback
    const feedbackDiv = reservationFeedback;
    const originalMsg = feedbackDiv.innerText;
    feedbackDiv.innerText = "âœ¨ Profile picture updated!";
    feedbackDiv.style.color = "#86e1a0";
    setTimeout(() => {
      if (feedbackDiv.innerText === "âœ¨ Profile picture updated!") {
        if (originalMsg && !originalMsg.includes("Reservation")) feedbackDiv.innerText = originalMsg;
        else feedbackDiv.innerText = "";
      }
    }, 1800);
  }

  // ----- Change Background (cover) -----
  function changeCoverBackground() {
    // dynamic fitness / gym related cover images
    const coverOptions = [
      'https://picsum.photos/id/82/1200/400?grayscale',
      'https://picsum.photos/id/133/1200/400',
      'https://picsum.photos/id/20/1200/400',
      'https://images.pexels.com/photos/841130/pexels-photo-841130.jpeg?auto=compress&cs=tinysrgb&w=1200&h=400&fit=crop',
      'https://images.pexels.com/photos/416249/pexels-photo-416249.jpeg?auto=compress&cs=tinysrgb&w=1200&h=400&fit=crop',
      'https://images.pexels.com/photos/260447/pexels-photo-260447.jpeg?auto=compress&cs=tinysrgb&w=1200&h=400&fit=crop'
    ];
    let randomCover = coverOptions[Math.floor(Math.random() * coverOptions.length)];
    coverImg.src = randomCover;
    // quick feedback
    const fb = reservationFeedback;
    const oldMsg = fb.innerText;
    fb.innerText = "ðŸ–¼ï¸ Background image changed!";
    fb.style.color = "#86e1a0";
    setTimeout(() => {
      if (fb.innerText === "ðŸ–¼ï¸ Background image changed!") {
        if (oldMsg && !oldMsg.includes("Reservation") && !oldMsg.includes("updated")) fb.innerText = oldMsg;
        else fb.innerText = "";
      }
    }, 1500);
  }

  // ----- Class selector logic (like selection for reservation) -----
  function initClassSelector() {
    classOptions.forEach(opt => {
      opt.addEventListener('click', function(e) {
        // remove active class from all
        classOptions.forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        selectedClass = this.getAttribute('data-class');
        // optional feedback clean
        reservationFeedback.innerText = `ðŸ“Œ ${selectedClass} selected. Tap "Reserve spot"`;
        reservationFeedback.style.color = "#b0b3b8";
        setTimeout(() => {
          if (reservationFeedback.innerText.includes("selected")) {
            if (!reservationFeedback.innerText.includes("Reserved")) 
              reservationFeedback.innerText = "";
          }
        }, 2000);
      });
    });
  }

  // ----- Reservation action (simulate booking) -----
  function reserveSpot() {
    const isStoreOn = storeCheckbox.checked; // just for fun, show extra detail
    const gymOpen = true;
    let message = `âœ… Reserved: ${selectedClass} class for tomorrow at 6:30 PM! `;
    if (isStoreOn) {
      message += `(Gym store is OPEN â€” grab your gear!) ðŸ›ï¸`;
    } else {
      message += `(Store is closed, but training is on!) ðŸ’ª`;
    }
    reservationFeedback.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    reservationFeedback.style.color = "#86e1a0";
    // reset after 4 seconds
    setTimeout(() => {
      if (reservationFeedback.innerHTML.includes("Reserved")) {
        reservationFeedback.innerHTML = "";
      }
    }, 4500);
  }

  // ----- store switch event & store status + panel management -----
  function bindStoreSwitch() {
    if (!storeCheckbox) {
      return;
    }

    storeCheckbox.addEventListener('change', function(e) {
      updateStoreUI();
      // also show toast-like message
      const msg = storeCheckbox.checked ? "ðŸ›’ Store Mode: ACTIVE Â· Check out our supplements & gear!" : "ðŸª Store Mode: CLOSED Â· Products hidden";
      const feedbackMsg = reservationFeedback;
      feedbackMsg.innerText = msg;
      feedbackMsg.style.color = "#b0b3b8";
      setTimeout(() => {
        if (reservationFeedback.innerText === msg) reservationFeedback.innerText = "";
      }, 2000);
    });
  }

  // ----- extra: store status text and panel initial state -----
  function initStore() {
    updateStoreUI();   // closed by default (checkbox unchecked)
  }

  function bindAboutEditor() {
    if (!aboutEditToggleBtn || !aboutModalOverlay || !aboutCancelBtn) {
      return;
    }

    aboutEditToggleBtn.addEventListener('click', () => {
      aboutModalOverlay.classList.add('active');
    });

    aboutCancelBtn.addEventListener('click', () => {
      aboutModalOverlay.classList.remove('active');
    });

    aboutModalOverlay.addEventListener('click', (event) => {
      if (event.target === aboutModalOverlay) {
        aboutModalOverlay.classList.remove('active');
      }
    });
  }

  function bindRulesEditor() {
    if (!rulesContent || !rulesModalOverlay || !rulesCancelBtn) {
      return;
    }

    rulesContent.addEventListener('click', () => {
      rulesModalOverlay.classList.add('active');
    });

    rulesCancelBtn.addEventListener('click', () => {
      rulesModalOverlay.classList.remove('active');
    });

    rulesModalOverlay.addEventListener('click', (event) => {
      if (event.target === rulesModalOverlay) {
        rulesModalOverlay.classList.remove('active');
      }
    });
  }

  function bindGymStatusButtons() {
    if (!gymToggleBtn) {
      return;
    }
  }

  function bindWalkInMembers() {
    if (!addWalkInBtn || !removeWalkInBtn || !reservationsList || !gymCapacityIndicator || !gymCapacityText) {
      return;
    }

    let current = Number(gymCapacityIndicator.getAttribute('data-current') || 0);
    const max = Number(gymCapacityIndicator.getAttribute('data-max') || 0);

    function updateCapacityText() {
      if (max > 0) {
        gymCapacityText.textContent = `${current} / ${max}`;
      } else {
        gymCapacityText.textContent = '0 / maximum capacity set by users';
      }
    }

    addWalkInBtn.addEventListener('click', () => {
      if (max <= 0) {
        reservationFeedback.textContent = 'Set max capacity first in Edit About.';
        reservationFeedback.style.color = '#f28b82';
        return;
      }

      if (current >= max) {
        reservationFeedback.textContent = 'Gym is at maximum capacity.';
        reservationFeedback.style.color = '#f28b82';
        return;
      }

      current += 1;
      gymCapacityIndicator.setAttribute('data-current', String(current));
      updateCapacityText();

      const item = document.createElement('div');
      item.className = 'reservation-item walkin-generated';
      item.innerHTML = `
        <div class="reservation-header">
          <div class="reservation-name">Walk-in Member #${current}</div>
          <div class="reservation-time">Just now</div>
        </div>
        <div class="reservation-detail">
          <i class="fas fa-user"></i> Unexpected walk-in added
        </div>
      `;
      reservationsList.prepend(item);

      reservationFeedback.textContent = `Walk-in added. Capacity: ${current}/${max}`;
      reservationFeedback.style.color = '#86e1a0';
    });

    removeWalkInBtn.addEventListener('click', () => {
      const latestWalkIn = reservationsList.querySelector('.walkin-generated');

      if (!latestWalkIn) {
        reservationFeedback.textContent = 'No walk-in member to remove.';
        reservationFeedback.style.color = '#f28b82';
        return;
      }

      latestWalkIn.remove();
      current = Math.max(current - 1, 0);
      gymCapacityIndicator.setAttribute('data-current', String(current));
      updateCapacityText();

      reservationFeedback.textContent = `Walk-in removed. Capacity: ${current}/${max}`;
      reservationFeedback.style.color = '#b0b3b8';
    });
  }

  function bindStoreManager() {
    if (!openAddProductModalBtn || !storeProductModalOverlay || !storeProductForm) {
      return;
    }

    function closeStoreProductModal() {
      storeProductModalOverlay.classList.remove('active');
      storeProductForm.reset();
      if (productImagePreview) {
        productImagePreview.src = '';
        productImagePreview.style.display = 'none';
      }
    }

    openAddProductModalBtn.addEventListener('click', () => {
      storeProductModalOverlay.classList.add('active');
    });

    if (storeProductCancelBtn) {
      storeProductCancelBtn.addEventListener('click', closeStoreProductModal);
    }

    storeProductModalOverlay.addEventListener('click', (event) => {
      if (event.target === storeProductModalOverlay) {
        closeStoreProductModal();
      }
    });

    if (productImageInput && productImagePreview) {
      productImageInput.addEventListener('change', () => {
        const file = productImageInput.files && productImageInput.files[0];
        if (!file) {
          productImagePreview.src = '';
          productImagePreview.style.display = 'none';
          return;
        }
        productImagePreview.src = URL.createObjectURL(file);
        productImagePreview.style.display = 'block';
      });
    }

    storeProductForm.addEventListener('submit', () => {
      // Form submits to backend for persistence.
    });

    function closeEditProductModal() {
      if (!editProductModalOverlay || !editProductForm) {
        return;
      }

      editProductModalOverlay.classList.remove('active');
      editProductForm.reset();
    }

    if (openEditProductModalBtns.length && editProductModalOverlay && editProductForm) {
      openEditProductModalBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
          const updateAction = btn.getAttribute('data-update-action') || '';
          const productName = btn.getAttribute('data-product-name') || '';
          const productPrice = btn.getAttribute('data-product-price') || '';
          const productStock = btn.getAttribute('data-product-stock') || '0';

          editProductForm.action = updateAction;
          if (editProductNameInput) editProductNameInput.value = productName;
          if (editProductPriceInput) editProductPriceInput.value = productPrice;
          if (editProductStockInput) editProductStockInput.value = productStock;
          if (editProductImageInput) editProductImageInput.value = '';

          editProductModalOverlay.classList.add('active');
        });
      });

      if (editProductCancelBtn) {
        editProductCancelBtn.addEventListener('click', closeEditProductModal);
      }

      editProductModalOverlay.addEventListener('click', (event) => {
        if (event.target === editProductModalOverlay) {
          closeEditProductModal();
        }
      });
    }
  }

  function bindSubscriptionManager() {
    if (!openAddSubscriptionModalBtn || !subscriptionModalOverlay || !subscriptionCancelBtn) {
      return;
    }

    openAddSubscriptionModalBtn.addEventListener('click', () => {
      subscriptionModalOverlay.classList.add('active');
    });

    subscriptionCancelBtn.addEventListener('click', () => {
      subscriptionModalOverlay.classList.remove('active');
    });

    subscriptionModalOverlay.addEventListener('click', (event) => {
      if (event.target === subscriptionModalOverlay) {
        subscriptionModalOverlay.classList.remove('active');
      }
    });

    if (setSubscriptionExpiryModalOverlay && setSubscriptionExpiryForm && setSubscriptionExpiresAtInput && setSubscriptionExpiryCancelBtn) {
      document.querySelectorAll('.subscription-expire-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
          const subscriptionId = btn.getAttribute('data-subscription-id');
          if (!subscriptionId) {
            return;
          }

          setSubscriptionExpiryForm.action = '{{ route('admin.business.subscriptions.expiry', ['business' => $business, 'subscription' => '__SUBSCRIPTION_ID__']) }}'.replace('__SUBSCRIPTION_ID__', subscriptionId);
          const now = new Date();
          const timezoneOffset = now.getTimezoneOffset() * 60000;
          const localNow = new Date(now.getTime() - timezoneOffset).toISOString().slice(0, 16);
          setSubscriptionExpiresAtInput.min = localNow;
          setSubscriptionExpiresAtInput.value = '';
          setSubscriptionExpiryModalOverlay.classList.add('active');
        });
      });

      setSubscriptionExpiryCancelBtn.addEventListener('click', () => {
        setSubscriptionExpiryModalOverlay.classList.remove('active');
      });

      setSubscriptionExpiryModalOverlay.addEventListener('click', (event) => {
        if (event.target === setSubscriptionExpiryModalOverlay) {
          setSubscriptionExpiryModalOverlay.classList.remove('active');
        }
      });
    }
  }

  function bindRealtimeSubscriptionExpiry() {
    if (!subscriptionsGrid) {
      return;
    }

    function parseLocalDateTime(value) {
      if (!value || !value.includes('T')) {
        return NaN;
      }

      const [datePart, timePart] = value.split('T');
      const [year, month, day] = datePart.split('-').map(Number);
      const [hour, minute, second = '0'] = timePart.split(':');

      return new Date(
        year,
        (month || 1) - 1,
        day || 1,
        Number(hour || 0),
        Number(minute || 0),
        Number(second || 0)
      ).getTime();
    }

    function formatRemaining(ms) {
      const totalSeconds = Math.floor(ms / 1000);
      const days = Math.floor(totalSeconds / 86400);
      const hours = Math.floor((totalSeconds % 86400) / 3600);
      const minutes = Math.floor((totalSeconds % 3600) / 60);

      if (days > 0) {
        return `${days}d ${hours}h`;
      }
      if (hours > 0) {
        return `${hours}h ${minutes}m`;
      }
      return `${Math.max(minutes, 0)}m`;
    }

    function updateEmptyState() {
      if (!subscriptionEmptyText) {
        return;
      }
      const hasCards = subscriptionsGrid.querySelectorAll('.subscription-card').length > 0;
      subscriptionEmptyText.style.display = hasCards ? 'none' : '';
    }

    function tick() {
      const now = Date.now();
      subscriptionsGrid.querySelectorAll('.subscription-card').forEach((card) => {
        const expiresAtRaw = card.getAttribute('data-expires-at');
        const valueEl = card.querySelector('.subscription-expire-value');

        if (!valueEl || !expiresAtRaw) {
          return;
        }

        const expiresMs = parseLocalDateTime(expiresAtRaw);
        if (Number.isNaN(expiresMs)) {
          valueEl.textContent = 'No expiry set';
          return;
        }

        const remaining = expiresMs - now;
        if (remaining <= 0) {
          card.remove();
          updateEmptyState();
          return;
        }

        valueEl.textContent = formatRemaining(remaining);
      });
    }

    tick();
    setInterval(tick, 1000);
  }

  // ----- initialize all interactive features & file upload alternative? we use random presets for simplicity (like facebook style but image change cycles) 
  // but to make it more interactive and similar to "change profile icon/background" without file input, we use preset random images.
  // However, we also can provide "upload via URL"? but requirement says "change profile icon, background" - we implement both random style (but gives user flexibility)
  // Additionally we can add click to upload file? We'll make it even better: double click also does random, but we can implement a simple file upload as alternative:
  // But we must ensure it works smooth. Let's add hidden file inputs for better UX: because user expects real image change.
  // Extra feature: For both cover and avatar, we can allow upload from device for real facebook style.
  // We'll add file upload via hidden inputs for PRO experience.
  
  // More realistic file upload for avatar & cover (modern)
  function setupFileUploads() {
    if (!changeAvatarBtn || !logoInput || !logoUploadForm || !changeCoverBtn || !coverInput || !coverUploadForm) {
      return;
    }

    changeAvatarBtn.addEventListener('click', () => {
      logoInput.click();
    });
    changeAvatarBtn.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        logoInput.click();
      }
    });
    logoInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file && file.type.startsWith('image/')) {
        setUploadFeedback('Saving profile photo...', '#b0b3b8');
        logoUploadForm.submit();
      } else {
        setUploadFeedback('Please select an image file', '#f28b82');
      }
    });
    
    changeCoverBtn.addEventListener('click', () => {
      coverInput.click();
    });
    changeCoverBtn.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        coverInput.click();
      }
    });
    coverInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file && file.type.startsWith('image/')) {
        setUploadFeedback('Saving cover image...', '#b0b3b8');
        coverUploadForm.submit();
      } else {
        setUploadFeedback('Invalid image for cover', '#f28b82');
      }
    });
  }

  // ----- bind navigation section tabs -----
  function bindNavigation() {
    const aboutGymSection = document.getElementById('aboutGymSection');
    const rulesSection = document.getElementById('rulesSection');
    const reservationsSection = document.getElementById('reservationsSection');
    const memberHistoriesSection = document.getElementById('memberHistoriesSection');
    const storeSection = document.getElementById('storeSection');
    const subscriptionsSection = document.getElementById('subscriptionsSection');
    const ordersSection = document.getElementById('ordersSection');
    const homeNavBtn = document.getElementById('homeNavBtn');
    const aboutReservationNavBtn = document.getElementById('aboutReservationNavBtn');
    const memberHistoryNavBtn = document.getElementById('memberHistoryNavBtn');
    const aboutStoreNavBtn = document.getElementById('aboutStoreNavBtn');
    const aboutSubscriptionNavBtn = document.getElementById('aboutSubscriptionNavBtn');
    const aboutOrdersNavBtn = document.getElementById('aboutOrdersNavBtn');
    
    const allNavBtns = [homeNavBtn, aboutReservationNavBtn, memberHistoryNavBtn, aboutStoreNavBtn, aboutSubscriptionNavBtn, aboutOrdersNavBtn];
    
    function removeActiveFromAll() {
      allNavBtns.forEach(btn => {
        if (btn) btn.classList.remove('active');
      });
    }
    
    function showAboutView() {
      removeActiveFromAll();
      if (homeNavBtn) homeNavBtn.classList.add('active');
      if (aboutGymSection) aboutGymSection.style.display = 'block';
      if (rulesSection) rulesSection.style.display = 'block';
      if (reservationsSection) reservationsSection.style.display = 'none';
      if (memberHistoriesSection) memberHistoriesSection.style.display = 'none';
      if (storeSection) storeSection.style.display = 'none';
      if (subscriptionsSection) subscriptionsSection.style.display = 'none';
      if (ordersSection) ordersSection.style.display = 'none';
    }
    
    function showReservationsView() {
      removeActiveFromAll();
      if (aboutReservationNavBtn) aboutReservationNavBtn.classList.add('active');
      if (aboutGymSection) aboutGymSection.style.display = 'none';
      if (rulesSection) rulesSection.style.display = 'none';
      if (reservationsSection) reservationsSection.style.display = 'block';
      if (memberHistoriesSection) memberHistoriesSection.style.display = 'none';
      if (storeSection) storeSection.style.display = 'none';
      if (subscriptionsSection) subscriptionsSection.style.display = 'none';
      if (ordersSection) ordersSection.style.display = 'none';
    }

    function showHistoryView() {
      removeActiveFromAll();
      if (memberHistoryNavBtn) memberHistoryNavBtn.classList.add('active');
      if (aboutGymSection) aboutGymSection.style.display = 'none';
      if (rulesSection) rulesSection.style.display = 'none';
      if (reservationsSection) reservationsSection.style.display = 'none';
      if (memberHistoriesSection) memberHistoriesSection.style.display = 'block';
      if (storeSection) storeSection.style.display = 'none';
      if (subscriptionsSection) subscriptionsSection.style.display = 'none';
      if (ordersSection) ordersSection.style.display = 'none';
    }

    function showStoreView() {
      removeActiveFromAll();
      if (aboutStoreNavBtn) aboutStoreNavBtn.classList.add('active');
      if (aboutGymSection) aboutGymSection.style.display = 'none';
      if (rulesSection) rulesSection.style.display = 'none';
      if (reservationsSection) reservationsSection.style.display = 'none';
      if (memberHistoriesSection) memberHistoriesSection.style.display = 'none';
      if (storeSection) storeSection.style.display = 'block';
      if (subscriptionsSection) subscriptionsSection.style.display = 'none';
      if (ordersSection) ordersSection.style.display = 'none';
    }

    function showSubscriptionsView() {
      removeActiveFromAll();
      if (aboutSubscriptionNavBtn) aboutSubscriptionNavBtn.classList.add('active');
      if (aboutGymSection) aboutGymSection.style.display = 'none';
      if (rulesSection) rulesSection.style.display = 'none';
      if (reservationsSection) reservationsSection.style.display = 'none';
      if (memberHistoriesSection) memberHistoriesSection.style.display = 'none';
      if (storeSection) storeSection.style.display = 'none';
      if (subscriptionsSection) subscriptionsSection.style.display = 'block';
      if (ordersSection) ordersSection.style.display = 'none';
    }

    function showOrdersView() {
      removeActiveFromAll();
      if (aboutOrdersNavBtn) aboutOrdersNavBtn.classList.add('active');
      if (aboutGymSection) aboutGymSection.style.display = 'none';
      if (rulesSection) rulesSection.style.display = 'none';
      if (reservationsSection) reservationsSection.style.display = 'none';
      if (memberHistoriesSection) memberHistoriesSection.style.display = 'none';
      if (storeSection) storeSection.style.display = 'none';
      if (subscriptionsSection) subscriptionsSection.style.display = 'none';
      if (ordersSection) ordersSection.style.display = 'block';
    }
    
    if (homeNavBtn) {
      homeNavBtn.addEventListener('click', (e) => {
        e.preventDefault();
        showAboutView();
      });
    }
    
    if (aboutReservationNavBtn) {
      aboutReservationNavBtn.addEventListener('click', () => {
        showReservationsView();
      });
    }
    
    if (memberHistoryNavBtn) {
      memberHistoryNavBtn.addEventListener('click', () => {
        showHistoryView();
      });
    }
    
    if (aboutStoreNavBtn) {
      aboutStoreNavBtn.addEventListener('click', () => {
        showStoreView();
      });
    }
    
    if (aboutSubscriptionNavBtn) {
      aboutSubscriptionNavBtn.addEventListener('click', () => {
        showSubscriptionsView();
      });
    }

    if (aboutOrdersNavBtn) {
      aboutOrdersNavBtn.addEventListener('click', () => {
        showOrdersView();
      });
    }
  }

  // ---- final initialization ----
  function init() {
    bindGymStatusButtons();
    bindRulesEditor();
    bindAboutEditor();
    bindStoreManager();
    bindSubscriptionManager();
    bindRealtimeSubscriptionExpiry();
    bindNavigation();

    initClassSelector();
    bindStoreSwitch();
    initStore();
    setupFileUploads();     // Enables real uploads for profile/background
    // attach reservation button (only when reservation panel exists)
    if (reserveBtn) {
      reserveBtn.addEventListener('click', reserveSpot);
    }
    // fallback if someone wants random quick change? but file upload is primary, we also keep classic random (optional double-click but not necessary)
    // but the change buttons are now used for file upload, exactly like changing profile icon/background. Perfect.
    // Extra tip: we also provide default random if user wants? No need, file upload is natural.
    // However requirement: "change profile icon, background" â€“ we fully support with file selection.
    // reservation selection is working and store switch.
    // We also add extra message to indicate file click.
  }
  
  init();
</script>
</body>
</html>