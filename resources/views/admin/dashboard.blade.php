@extends('layouts.admin')
@section('title', 'Sartel-E || Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
    <style>
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
                margin-bottom: 20px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-icon {
                width: 36px;
                height: 36px;
                font-size: 15px;
                margin-bottom: 10px;
                border-radius: 10px;
            }

            .stat-value {
                font-size: 22px;
            }

            .stat-label {
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')

    <div class="stats-grid">

        <div class="stat-card blue">
            <div class="stat-icon blue"><i class="fas fa-user-md"></i></div>
            <div class="stat-value">{{ $totalDoctors }}</div>
            <div class="stat-label">Total Doctors</div>
        </div>

        <div class="stat-card red">
            <div class="stat-icon red"><i class="fas fa-heart-pulse"></i></div>
            <div class="stat-value">{{ number_format($worldHeartDayTotal) }}</div>
            <div class="stat-label">World Heart Day Records</div>
        </div>

        <div class="stat-card yellow">
            <div class="stat-icon yellow"><i class="fas fa-images"></i></div>
            <div class="stat-value">{{ number_format($worldHeartDayPhotos) }}</div>
            <div class="stat-label">World Heart Day S3 Photos</div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon green"><i class="fas fa-panorama"></i></div>
            <div class="stat-value">{{ number_format($worldHeartDayBanners) }}</div>
            <div class="stat-label">World Heart Day Ready Banners</div>
        </div>

    </div>

@endsection
