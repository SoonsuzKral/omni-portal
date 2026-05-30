@once
<style>
#adblock-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(8px);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: adblock-fade-in 0.3s ease-out;
}

@keyframes adblock-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

.adblock-modal-wrapper {
    width: 100%;
    max-width: 480px;
    animation: adblock-slide-up 0.4s ease-out;
}

@keyframes adblock-slide-up {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.adblock-modal {
    background: #ffffff;
    border-radius: 24px;
    padding: 48px 40px;
    text-align: center;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
    position: relative;
    overflow: hidden;
}

.adblock-modal::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #dc2626, #ea580c, #f59e0b);
}

.adblock-modal-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 28px;
}

.adblock-modal-icon svg {
    width: 50px;
    height: 50px;
    color: #dc2626;
}

.adblock-modal-title {
    font-size: 26px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 16px;
    letter-spacing: -0.5px;
}

.adblock-modal-desc {
    color: #4b5563;
    font-size: 16px;
    line-height: 1.7;
    margin-bottom: 32px;
}

.adblock-modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.adblock-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 24px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-family: inherit;
}

.adblock-btn svg {
    width: 18px;
    height: 18px;
}

.adblock-btn-primary {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: white;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
}

.adblock-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
}

.adblock-btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.adblock-btn-secondary:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
}

.adblock-modal-checking .adblock-modal-actions {
    opacity: 0.5;
    pointer-events: none;
}

@media (max-width: 480px) {
    .adblock-modal {
        padding: 36px 24px;
    }

    .adblock-modal-title {
        font-size: 22px;
    }

    .adblock-modal-desc {
        font-size: 14px;
    }

    .adblock-btn {
        width: 100%;
        justify-content: center;
    }

    .adblock-modal-actions {
        flex-direction: column;
    }
}

.ad-blocked-notice {
    padding: 20px;
    text-align: center;
    color: #9ca3af;
    font-size: 13px;
    background: #f9fafb;
    border-radius: 8px;
}

.adblock-spinner {
    width: 18px;
    height: 18px;
    border: 2px solid #e5e7eb;
    border-top-color: #6b7280;
    border-radius: 50%;
    animation: adblock-spin 0.8s linear infinite;
}

@keyframes adblock-spin {
    to { transform: rotate(360deg); }
}

.adblock-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
@endonce