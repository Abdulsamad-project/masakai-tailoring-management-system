<?php 
require_once __DIR__ . '/includes/db.php'; 
require_once __DIR__ . '/includes/functions.php'; 
?> 

<!DOCTYPE html> 

<html lang="en"> 
<head> 
<meta charset="UTF-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0"> 

<title>Masakai Tailoring Service — Precision Crafted, Perfectly Fitted</title> 

<link rel="stylesheet" href="assets/css/style.css"> 

<style> 
/* ========================================================= 
   MASAKAI LANDING PAGE — LUXURY ATELIER 
   ========================================================= */ 
 
:root { 
    --atelier-dark: #171512; 
    --atelier-black: #0f0e0c; 
    --atelier-gold: #b89452; 
    --atelier-gold-light: #d5b875; 
    --atelier-cream: #f7f3eb; 
    --atelier-white: #ffffff; 
    --atelier-muted: #77736c; 
    --atelier-border: rgba(23, 21, 18, 0.12); 
} 
 
/* ---------- HERO ---------- */ 
 
.atelier-hero { 
    background: var(--atelier-cream); 
    padding: 70px 0 80px; 
    overflow: hidden; 
} 
 
.atelier-hero-inner { 
    display: grid; 
    grid-template-columns: 0.9fr 1.1fr; 
    gap: 70px; 
    align-items: center; 
} 
 
.atelier-hero-copy { 
    max-width: 620px; 
} 
 
.atelier-eyebrow { 
    display: inline-flex; 
    align-items: center; 
    gap: 12px; 
    margin-bottom: 24px; 
    font-size: 12px; 
    font-weight: 700; 
    letter-spacing: 0.18em; 
    color: var(--atelier-gold); 
} 
 
.atelier-eyebrow::before { 
    content: ""; 
    width: 35px; 
    height: 1px; 
    background: var(--atelier-gold); 
} 
 
.atelier-hero h1 { 
    margin: 0; 
    font-size: clamp(48px, 6vw, 82px); 
    line-height: 0.98; 
    letter-spacing: -0.045em; 
    font-weight: 700; 
    color: var(--atelier-dark); 
} 
 
.atelier-hero h1 em { 
    font-family: Georgia, "Times New Roman", serif; 
    font-weight: 400; 
    color: var(--atelier-gold); 
} 
 
.atelier-hero-text { 
    max-width: 560px; 
    margin: 28px 0 32px; 
    font-size: 17px; 
    line-height: 1.8; 
    color: #625e57; 
} 
 
.atelier-hero-actions { 
    display: flex; 
    flex-wrap: wrap; 
    gap: 14px; 
} 
 
.atelier-hero-actions .btn-gold { 
    text-decoration: none; 
} 
 
.atelier-secondary-btn { 
    display: inline-flex; 
    align-items: center; 
    justify-content: center; 
    padding: 13px 23px; 
    border: 1px solid var(--atelier-dark); 
    color: var(--atelier-dark); 
    text-decoration: none; 
    font-weight: 600; 
    transition: 0.25s ease; 
} 
 
.atelier-secondary-btn:hover { 
    background: var(--atelier-dark); 
    color: white; 
} 
 
/* Hero image */ 
 
.atelier-hero-image { 
    position: relative; 
    min-height: 610px; 
} 
 
.atelier-hero-image-frame { 
    position: absolute; 
    inset: 0 0 0 35px; 
    overflow: hidden; 
    background: #ddd; 
} 
 
.atelier-hero-image-frame img { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
    display: block; 
} 
 
.atelier-image-label { 
    position: absolute; 
    left: 0; 
    bottom: 32px; 
    background: var(--atelier-dark); 
    color: white; 
    padding: 18px 24px; 
    min-width: 190px; 
} 
 
.atelier-image-label small { 
    display: block; 
    margin-bottom: 5px; 
    color: var(--atelier-gold-light); 
    font-size: 10px; 
    letter-spacing: 0.16em; 
    font-weight: 700; 
} 
 
.atelier-image-label strong { 
    font-size: 14px; 
    font-weight: 600; 
} 
 
/* ---------- ATELIER STRIP ---------- */ 
 
.atelier-strip { 
    background: var(--atelier-dark); 
    color: white; 
    padding: 20px 0; 
} 
 
.atelier-strip-inner { 
    display: flex; 
    justify-content: center; 
    flex-wrap: wrap; 
    gap: 0; 
} 
 
.atelier-strip-item { 
    padding: 0 30px; 
    font-size: 12px; 
    letter-spacing: 0.13em; 
    text-transform: uppercase; 
    color: #ddd5c7; 
    border-right: 1px solid rgba(255,255,255,0.18); 
} 
 
.atelier-strip-item:last-child { 
    border-right: 0; 
} 
 
/* ---------- CRAFTSMANSHIP ---------- */ 
 
.craft-section { 
    padding: 110px 0; 
    background: white; 
} 
 
.craft-grid { 
    display: grid; 
    grid-template-columns: 1fr 0.9fr; 
    gap: 80px; 
    align-items: center; 
} 
 
.craft-image { 
    position: relative; 
    min-height: 520px; 
} 
 
.craft-image img { 
    width: 100%; 
    height: 520px; 
    object-fit: cover; 
    display: block; 
} 
 
.craft-image::after { 
    content: ""; 
    position: absolute; 
    left: -18px; 
    bottom: -18px; 
    width: 110px; 
    height: 110px; 
    border-left: 1px solid var(--atelier-gold); 
    border-bottom: 1px solid var(--atelier-gold); 
    pointer-events: none; 
} 
 
.craft-copy { 
    max-width: 530px; 
} 
 
.craft-kicker { 
    color: var(--atelier-gold); 
    font-size: 11px; 
    letter-spacing: 0.2em; 
    font-weight: 700; 
    margin-bottom: 18px; 
} 
 
.craft-copy h2 { 
    font-family: Georgia, "Times New Roman", serif; 
    font-size: clamp(38px, 4vw, 56px); 
    line-height: 1.05; 
    font-weight: 400; 
    color: var(--atelier-dark); 
    margin: 0 0 24px; 
} 
 
.craft-copy p { 
    color: var(--atelier-muted); 
    line-height: 1.85; 
    font-size: 16px; 
    margin-bottom: 18px; 
} 
 
.craft-details { 
    display: grid; 
    grid-template-columns: repeat(3, 1fr); 
    gap: 15px; 
    margin-top: 35px; 
} 
 
.craft-detail { 
    border-top: 1px solid var(--atelier-border); 
    padding-top: 15px; 
} 
 
.craft-detail strong { 
    display: block; 
    color: var(--atelier-dark); 
    font-size: 13px; 
    margin-bottom: 5px; 
} 
 
.craft-detail span { 
    color: var(--atelier-muted); 
    font-size: 11px; 
    line-height: 1.5; 
} 
 
/* ---------- SERVICES ---------- */ 
 
.atelier-services { 
    background: var(--atelier-cream); 
    padding: 110px 0; 
} 
 
.atelier-heading { 
    max-width: 680px; 
    margin-bottom: 55px; 
} 
 
.atelier-heading .kicker { 
    color: var(--atelier-gold); 
    font-size: 11px; 
    letter-spacing: 0.2em; 
    font-weight: 700; 
    margin-bottom: 15px; 
} 
 
.atelier-heading h2 { 
    font-family: Georgia, "Times New Roman", serif; 
    font-size: clamp(40px, 5vw, 60px); 
    line-height: 1.05; 
    font-weight: 400; 
    color: var(--atelier-dark); 
    margin: 0 0 18px; 
} 
 
.atelier-heading p { 
    color: var(--atelier-muted); 
    line-height: 1.8; 
    margin: 0; 
} 
 
/* Main garment cards */ 
 
.garment-grid { 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    gap: 18px; 
} 
 
.garment-card { 
    background: white; 
    text-decoration: none; 
    color: inherit; 
    overflow: hidden; 
    transition: transform 0.25s ease, box-shadow 0.25s ease; 
} 
 
.garment-card:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 18px 45px rgba(0,0,0,0.08); 
} 
 
.garment-image { 
    height: 310px; 
    overflow: hidden; 
} 
 
.garment-image img { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
    display: block; 
    transition: transform 0.5s ease; 
} 
 
.garment-card:hover .garment-image img { 
    transform: scale(1.04); 
} 
 
.garment-info { 
    padding: 22px; 
} 
 
.garment-number { 
    color: var(--atelier-gold); 
    font-size: 10px; 
    letter-spacing: 0.15em; 
} 
 
.garment-info h3 { 
    margin: 8px 0 7px; 
    color: var(--atelier-dark); 
    font-size: 20px; 
} 
 
.garment-info p { 
    margin: 0; 
    color: var(--atelier-muted); 
    font-size: 13px; 
    line-height: 1.6; 
} 
 
/* Secondary services */ 
 
.secondary-services { 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    border-top: 1px solid var(--atelier-border); 
    margin-top: 45px; 
} 
 
.secondary-service { 
    padding: 28px 25px; 
    border-right: 1px solid var(--atelier-border); 
} 
 
.secondary-service:last-child { 
    border-right: 0; 
} 
 
.secondary-service h3 { 
    font-size: 16px; 
    color: var(--atelier-dark); 
    margin: 0 0 8px; 
} 
 
.secondary-service p { 
    color: var(--atelier-muted); 
    font-size: 13px; 
    line-height: 1.6; 
    margin: 0; 
} 
 
/* ---------- LOGIN ---------- */ 
 
.atelier-access { 
    padding: 95px 0; 
    background: var(--atelier-dark); 
    color: white; 
} 
 
.atelier-access-inner { 
    max-width: 850px; 
    margin: auto; 
    text-align: center; 
} 
 
.atelier-access .kicker { 
    color: var(--atelier-gold-light); 
    font-size: 11px; 
    letter-spacing: 0.2em; 
    font-weight: 700; 
} 
 
.atelier-access h2 { 
    font-family: Georgia, "Times New Roman", serif; 
    font-weight: 400; 
    font-size: clamp(38px, 5vw, 58px); 
    margin: 15px 0; 
} 
 
.atelier-access p { 
    max-width: 600px; 
    margin: 0 auto 32px; 
    color: #c9c4bb; 
    line-height: 1.8; 
} 
 
.atelier-access-card { 
    max-width: 560px; 
    margin: auto; 
    padding: 35px; 
    border: 1px solid rgba(255,255,255,0.16); 
    background: rgba(255,255,255,0.035); 
} 
 
.atelier-access-card a { 
    display: block; 
    text-decoration: none; 
} 
 
.atelier-access-title { 
    color: white; 
    font-size: 22px; 
    font-weight: 600; 
    margin-bottom: 8px; 
} 
 
.atelier-access-copy { 
    color: #aaa59d; 
    font-size: 13px; 
    line-height: 1.7; 
    margin-bottom: 24px; 
} 
 
/* ---------- HOW IT WORKS ---------- */ 
 
.atelier-process { 
    padding: 110px 0; 
    background: white; 
} 
 
.process-layout { 
    display: grid; 
    grid-template-columns: 0.85fr 1.15fr; 
    gap: 75px; 
    align-items: center; 
} 
 
.process-photo { 
    height: 500px; 
    overflow: hidden; 
} 
 
.process-photo img { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
    display: block; 
} 
 
.process-content .kicker { 
    color: var(--atelier-gold); 
    font-size: 11px; 
    letter-spacing: 0.2em; 
    font-weight: 700; 
} 
 
.process-content h2 { 
    font-family: Georgia, "Times New Roman", serif; 
    font-size: clamp(38px, 4vw, 54px); 
    line-height: 1.05; 
    font-weight: 400; 
    margin: 14px 0 35px; 
} 
 
.process-step { 
    display: grid; 
    grid-template-columns: 55px 1fr; 
    gap: 18px; 
    padding: 22px 0; 
    border-top: 1px solid var(--atelier-border); 
} 
 
.process-step:last-child { 
    border-bottom: 1px solid var(--atelier-border); 
} 
 
.process-number { 
    color: var(--atelier-gold); 
    font-family: Georgia, serif; 
    font-size: 22px; 
} 
 
.process-step h3 { 
    margin: 0 0 6px; 
    font-size: 17px; 
} 
 
.process-step p { 
    margin: 0; 
    color: var(--atelier-muted); 
    font-size: 13px; 
    line-height: 1.65; 
} 
 
/* ---------- COLLECTION BANNER ---------- */ 
 
.collection-section { 
    padding: 0; 
    background: var(--atelier-dark); 
} 
 
.collection-image { 
    position: relative; 
    height: 480px; 
    overflow: hidden; 
} 
 
.collection-image img { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
    display: block; 
} 
 
.collection-overlay { 
    position: absolute; 
    inset: 0; 
    background: linear-gradient( 
        90deg, 
        rgba(15,14,12,0.82), 
        rgba(15,14,12,0.20) 
    ); 
} 
 
.collection-copy { 
    position: absolute; 
    left: max(25px, calc((100% - 1200px) / 2)); 
    top: 50%; 
    transform: translateY(-50%); 
    max-width: 510px; 
    color: white; 
} 
 
.collection-copy .kicker { 
    color: var(--atelier-gold-light); 
    font-size: 11px; 
    letter-spacing: 0.2em; 
    font-weight: 700; 
} 
 
.collection-copy h2 { 
    font-family: Georgia, "Times New Roman", serif; 
    font-weight: 400; 
    font-size: clamp(40px, 5vw, 60px); 
    line-height: 1; 
    margin: 14px 0 18px; 
} 
 
.collection-copy p { 
    color: #ddd8cf; 
    line-height: 1.7; 
} 
 
/* ---------- TESTIMONIALS ---------- */ 
 
.atelier-testimonials { 
    background: var(--atelier-cream); 
    padding: 100px 0; 
} 
 
.atelier-testimonial-grid { 
    display: grid; 
    grid-template-columns: repeat(3, 1fr); 
    gap: 20px; 
} 
 
.atelier-testimonial { 
    background: white; 
    padding: 30px; 
    border-top: 2px solid var(--atelier-gold); 
} 
 
.atelier-testimonial-stars { 
    color: var(--atelier-gold); 
    letter-spacing: 3px; 
    margin-bottom: 20px; 
} 
 
.atelier-testimonial p { 
    color: #555149; 
    font-family: Georgia, serif; 
    font-size: 17px; 
    line-height: 1.75; 
} 
 
.atelier-testimonial-author { 
    margin-top: 20px; 
    color: var(--atelier-dark); 
    font-size: 12px; 
    font-weight: 700; 
    letter-spacing: 0.08em; 
} 
 
/* ---------- RESPONSIVE ---------- */ 
 
@media (max-width: 1000px) { 
 
    .atelier-hero-inner { 
        grid-template-columns: 1fr; 
        gap: 45px; 
    } 
 
    .atelier-hero-image { 
        min-height: 520px; 
    } 
 
    .craft-grid, 
    .process-layout { 
        grid-template-columns: 1fr; 
        gap: 50px; 
    } 
 
    .garment-grid { 
        grid-template-columns: repeat(2, 1fr); 
    } 
 
    .secondary-services { 
        grid-template-columns: repeat(2, 1fr); 
    } 
 
    .secondary-service:nth-child(2) { 
        border-right: 0; 
    } 
 
    .secondary-service:nth-child(n+3) { 
        border-top: 1px solid var(--atelier-border); 
    } 
 
    .atelier-testimonial-grid { 
        grid-template-columns: 1fr; 
    } 
} 
 
@media (max-width: 650px) { 
 
    .atelier-hero { 
        padding: 45px 0 55px; 
    } 
 
    .atelier-hero h1 { 
        font-size: 48px; 
    } 
 
    .atelier-hero-image { 
        min-height: 430px; 
    } 
 
    .atelier-hero-image-frame { 
        inset: 0; 
    } 
 
    .atelier-image-label { 
        left: 15px; 
        bottom: 15px; 
    } 
 
    .atelier-strip-item { 
        padding: 8px 14px; 
        font-size: 9px; 
    } 
 
    .craft-section, 
    .atelier-services, 
    .atelier-process, 
    .atelier-testimonials { 
        padding: 75px 0; 
    } 
 
    .craft-image, 
    .craft-image img { 
        min-height: 390px; 
        height: 390px; 
    } 
 
    .craft-details { 
        grid-template-columns: 1fr; 
    } 
 
    .garment-grid { 
        grid-template-columns: 1fr; 
    } 
 
    .garment-image { 
        height: 360px; 
    } 
 
    .secondary-services { 
        grid-template-columns: 1fr; 
    } 
 
    .secondary-service { 
        border-right: 0; 
        border-bottom: 1px solid var(--atelier-border); 
    } 
 
    .secondary-service:nth-child(n+2) { 
        border-top: 0; 
    } 
 
    .process-photo { 
        height: 390px; 
    } 
 
    .collection-image { 
        height: 430px; 
    } 
 
    .collection-copy { 
        left: 25px; 
        right: 25px; 
    } 
 
    .atelier-access { 
        padding: 75px 0; 
    } 
 
    .atelier-access-card { 
        padding: 25px 20px; 
    } 
} 
</style> 

</head> 

<body> 

<nav class="navbar"> 
    <div class="container nav-inner"> 

        <a href="index.php" class="nav-brand"> 
            &#9997; Masakai 
            <small>| Tailoring Service</small> 
        </a> 

        <div class="nav-links"> 
            <a href="#services">Services</a> 
            <a href="#how-it-works">How It Works</a> 
            <a href="track_status.php">Track Order</a> 
            <a href="login.php" class="nav-cta">Login</a> 
        </div> 

    </div> 
</nav> 

<section class="atelier-hero"> 

    <div class="container atelier-hero-inner"> 

        <div class="atelier-hero-copy"> 

            <div class="atelier-eyebrow"> 
                MASAKAI TAILORING SERVICE 
            </div> 

            <h1> 
                Crafted to 
                <em>Perfection.</em> 
                Made for You. 
            </h1> 

            <p class="atelier-hero-text"> 
                Bespoke tailoring rooted in traditional craftsmanship, 
                refined with modern precision. From distinguished 
                Agbada and Babban Riga to contemporary formal wear, 
                every garment is made to your measurements. 
            </p> 

            <div class="atelier-hero-actions"> 

                <a href="track_status.php" class="btn-gold"> 
                    Track My Order 
                </a> 

                <a href="#services" class="atelier-secondary-btn"> 
                    Explore Our Work 
                </a> 

            </div> 

        </div> 

        <div class="atelier-hero-image"> 

            <div class="atelier-hero-image-frame"> 

                <img 
                    src="assets/agbada.jfif" 
                    alt="Bespoke Agbada crafted by Masakai" 
                    fetchpriority="high" 
                > 

            </div> 

            <div class="atelier-image-label"> 

                <small>FEATURED CRAFT</small> 

                <strong> 
                    Bespoke Agbada 
                </strong> 

            </div> 

        </div> 

    </div> 

</section> 

<div class="atelier-strip"> 

    <div class="container atelier-strip-inner"> 

        <div class="atelier-strip-item">Agbada</div> 
        <div class="atelier-strip-item">Babban Riga</div> 
        <div class="atelier-strip-item">Kaftan</div> 
        <div class="atelier-strip-item">Formal Wear</div> 
        <div class="atelier-strip-item">Bespoke Craft</div> 

    </div> 

</div> 

<section class="craft-section"> 

    <div class="container craft-grid"> 

        <div class="craft-image"> 

            <img 
                src="assets/tailor-working.jpg" 
                alt="Masakai tailor carefully working on a garment" 
                loading="lazy" 
            > 

        </div> 

        <div class="craft-copy"> 

            <div class="craft-kicker">THE MASAKAI STANDARD</div> 

            <h2>Where craft meets precision.</h2> 

            <p> 
                A great garment begins long before the final stitch. 
                Our process combines careful measurement, thoughtful 
                cutting and skilled finishing to create clothing that 
                feels personal from the first fitting. 
            </p> 

            <p> 
                Traditional Nigerian tailoring remains at the heart of 
                Masakai, while our service is built around a modern, 
                organised experience for every customer. 
            </p> 

            <div class="craft-details"> 

                <div class="craft-detail"> 
                    <strong>Precise</strong> 
                    <span>Measurements recorded carefully for your garment.</span> 
                </div> 

                <div class="craft-detail"> 
                    <strong>Handcrafted</strong> 
                    <span>Skilled tailoring from cutting to finishing.</span> 
                </div> 

                <div class="craft-detail"> 
                    <strong>Trackable</strong> 
                    <span>Follow your order as it moves through production.</span> 
                </div> 

            </div> 

        </div> 

    </div> 

</section> 

<section class="atelier-services" id="services"> 

    <div class="container"> 

        <div class="atelier-heading"> 

            <div class="kicker">OUR SPECIALITIES</div> 

            <h2>Garments with presence.</h2> 

            <p> 
                From heritage silhouettes to modern formalwear, 
                each piece is cut around the person wearing it. 
            </p> 

        </div> 

        <div class="garment-grid"> 

            <!-- 01 — BABban RIGA -->
            <article class="garment-card"> 

                <div class="garment-image"> 
                    <img 
                        src="assets/babban-riga.jfif" 
                        alt="Bespoke Babban Riga" 
                        loading="lazy" 
                    > 
                </div> 

                <div class="garment-info"> 

                    <span class="garment-number">01</span> 

                    <h3>Babban Riga</h3> 

                    <p> 
                        Refined northern robes crafted with 
                        proportion, detail and character. 
                    </p> 

                </div> 

            </article> 

            <!-- 02 — AGBADA -->
            <article class="garment-card"> 

                <div class="garment-image"> 
                    <img 
                        src="assets/agbada.jfif" 
                        alt="Bespoke Agbada" 
                        loading="lazy" 
                    > 
                </div> 

                <div class="garment-info"> 

                    <span class="garment-number">02</span> 

                    <h3>Agbada</h3> 

                    <p> 
                        Statement three-piece attire made for 
                        celebrations and special occasions. 
                    </p> 

                </div> 

            </article> 

            <!-- 03 — TRADITIONAL WEAR -->
            <article class="garment-card"> 

                <div class="garment-image"> 
                    <img 
                        src="assets/babban-riga%202.jfif" 
                        alt="Masakai traditional garment" 
                        loading="lazy" 
                    > 
                </div> 

                <div class="garment-info"> 

                    <span class="garment-number">03</span> 

                    <h3>Traditional Wear</h3> 

                    <p> 
                        Distinctive designs inspired by 
                        Nigerian tailoring traditions. 
                    </p> 

                </div> 

            </article> 

            <!-- 04 — FORMAL SUITS -->
            <article class="garment-card"> 

                <div class="garment-image"> 
                    <img 
                        src="assets/suits.jfif" 
                        alt="Bespoke formal suit by Masakai" 
                        loading="lazy" 
                    > 
                </div> 

                <div class="garment-info"> 

                    <span class="garment-number">04</span> 

                    <h3>Formal Suits</h3> 

                    <p> 
                        Precisely tailored formalwear designed for 
                        weddings, business and special occasions. 
                    </p> 

                </div> 

            </article> 

        </div> 

        <div class="secondary-services"> 

            <div class="secondary-service"> 
                <h3>Bridal Wear</h3> 
                <p> 
                    Bespoke wedding and bridal-party garments 
                    made around your occasion. 
                </p> 
            </div> 

            <div class="secondary-service"> 
                <h3>Alterations</h3> 
                <p> 
                    Professional resizing, adjustments, 
                    repairs and finishing. 
                </p> 
            </div> 

            <div class="secondary-service"> 
                <h3>Casual Wear</h3> 
                <p> 
                    Everyday pieces combining comfort, 
                    character and clean construction. 
                </p> 
            </div> 

            <div class="secondary-service"> 
                <h3>Custom Requests</h3> 
                <p> 
                    Bring your idea, fabric or reference 
                    and let us bring it to life. 
                </p> 
            </div> 

        </div> 

    </div> 

</section> 

<section class="atelier-access" id="workspace"> 

    <div class="container atelier-access-inner"> 

        <div class="kicker">SECURE ACCESS</div> 

        <h2>Masakai Management Portal</h2> 

        <p> 
            Sign in with your Masakai account to access your 
            workspace. Your account type is detected automatically. 
        </p> 

        <div class="atelier-access-card"> 

            <div class="atelier-access-title">Sign In to Masakai</div> 

            <div class="atelier-access-copy"> 
                Admin, receptionist, tailor and customer accounts 
                all use the same secure login. 
            </div> 

            <a href="login.php" class="btn-gold btn-full"> 
                Continue to Login → 
            </a> 

        </div> 

    </div> 

</section> 

<section class="atelier-process" id="how-it-works"> 

    <div class="container process-layout"> 

        <div class="process-photo"> 

            <img 
                src="assets/tailor-working.jpg" 
                alt="Tailor working on a Masakai garment" 
                loading="lazy" 
            > 

        </div> 

        <div class="process-content"> 

            <div class="kicker">THE PROCESS</div> 

            <h2>From idea to finished garment.</h2> 

            <div class="process-step"> 

                <div class="process-number">01</div> 

                <div> 
                    <h3>Consultation</h3> 
                    <p> 
                        Discuss your style, fabric preference 
                        and requirements with our team. 
                    </p> 
                </div> 

            </div> 

            <div class="process-step"> 

                <div class="process-number">02</div> 

                <div> 
                    <h3>Measurements</h3> 
                    <p> 
                        Your measurements are carefully recorded 
                        to establish the foundation of the garment. 
                    </p> 
                </div> 

            </div> 

            <div class="process-step"> 

                <div class="process-number">03</div> 

                <div> 
                    <h3>Crafting</h3> 
                    <p> 
                        The garment moves through cutting, 
                        sewing and finishing. 
                    </p> 
                </div> 

            </div> 

            <div class="process-step"> 

                <div class="process-number">04</div> 

                <div> 
                    <h3>Collection</h3> 
                    <p> 
                        Track your order and collect your 
                        finished garment when it is ready. 
                    </p> 
                </div> 

            </div> 

        </div> 

    </div> 

</section> 

<section class="collection-section"> 

    <div class="collection-image"> 

        <img 
            src="assets/babban-riga%202.jfif" 
            alt="Finished traditional garment by Masakai" 
            loading="lazy" 
        > 

        <div class="collection-overlay"></div> 

        <div class="collection-copy"> 

            <div class="kicker">MADE FOR THE OCCASION</div> 

            <h2>Wear something that feels like yours.</h2> 

            <p> 
                Thoughtful design, careful construction and 
                traditional tailoring brought together in one garment. 
            </p> 

        </div> 

    </div>

</section> 

<section class="atelier-testimonials"> 

    <div class="container"> 

        <div class="atelier-heading"> 

            <div class="kicker">CLIENT EXPERIENCES</div> 

            <h2>What our clients say.</h2> 

            <p> 
                The finished garment matters. So does the experience 
                of getting there. 
            </p> 

        </div> 

        <div class="atelier-testimonial-grid"> 

            <?php 

            $tests = [ 

                [ 
                    'My Agbada for Sallah was absolutely perfect. 
                    The embroidery detail was stunning — exactly 
                    as I described. Masakai Tailoring Service never disappoints.', 
                    'Alhaji Musa B., Sokoto' 
                ], 

                [ 
                    'I tracked my order from my phone and could see 
                    its progress. The whole process felt organised 
                    and professional.', 
                    'Fatima A., Kano' 
                ], 

                [ 
                    'They made my slim-fit suit for my wedding. 
                    Every measurement was honoured and the final 
                    fit was excellent.', 
                    'Emmanuel O., Abuja' 
                ] 

            ]; 

            foreach ($tests as [$quote, $author]): 
            ?> 

                <article class="atelier-testimonial"> 

                    <div class="atelier-testimonial-stars"> 
                        ★★★★★ 
                    </div> 

                    <p> 
                        “<?= $quote ?>” 
                    </p> 

                    <div class="atelier-testimonial-author"> 
                        <?= $author ?> 
                    </div> 

                </article> 

            <?php endforeach; ?> 

        </div> 

    </div> 

</section> 

<footer class="footer"> 

    <div class="container"> 

        <div class="footer-inner"> 

            <div> 

                <div class="footer-brand"> 
                    Masakai Tailoring Service 
                </div> 

                <div class="footer-tag"> 
                    Precision Crafted. Perfectly Fitted. 
                </div> 

            </div> 

            <div class="footer-col"> 

                <h4>Services</h4> 
                <a href="#services">Babban Riga</a> 
                <a href="#services">Agbada</a> 
                <a href="#services">Kaftan</a> 
                <a href="#services">Formal Suits</a> 

            </div> 

            <div class="footer-col"> 

                <h4>Quick Links</h4> 
                <a href="track_status.php">Track My Order</a> 
                <a href="#how-it-works">How It Works</a> 
                <a href="login.php">Login</a> 

            </div> 

            <div class="footer-col"> 

                <h4>Contact Us</h4> 

                <a href="#">14 Tailor Lane, Sokoto</a> 
                <a href="tel:08000000000">0800-MASAKAI</a> 
                <a href="mailto:hello@masakai.ng">hello@masakai.ng</a> 

            </div> 

        </div> 

        <div class="footer-bottom"> 

            &copy; <?= date('Y') ?> 
            Masakai Tailoring Service. 
            All rights reserved. 

        </div> 

    </div> 

</footer> 

<script src="assets/js/script.js"></script> 

</body> 
</html>