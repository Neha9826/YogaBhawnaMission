<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <!-- 
    modal-dialog-scrollable makes the *modal itself* scroll if content is too long.
    modal-lg gives it a bit more width.
    -->
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="filterModalLabel">Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <!-- 
                I HAVE COPIED YOUR *ENTIRE* FILTER SIDEBAR HTML HERE.
                This ensures all your filters, accordions, and styles work
                inside the modal just as they do on the sidebar.
            -->
            <div class="filter-sidebar" id="filter-sidebar-modal"> <!-- Note: Changed ID to avoid duplicates -->
                <form id="filterFormModal" action="index.php" method="GET"> <!-- Note: Changed ID -->
                    <!-- I'm removing the PHP for this HTML example, but you'd keep it -->
                    <input type="hidden" name="q" value="">

                    <h4 style="padding-bottom: 1rem;" class="filter-title-bold">Filters</h4>

                    <h5 class="filter-title">Deals</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="deal" id="deal_all_modal"><label class="form-check-label" for="deal_all_modal">All deals (1303)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="deal" id="deal_early_modal"><label class="form-check-label" for="deal_early_modal">Early bird (569)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="deal" id="deal_exclusive_modal"><label class="form-check-label" for="deal_exclusive_modal">Exclusive Gifts (443)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="deal" id="deal_special_modal"><label class="form-check-label" for="deal_special_modal">Special offer (174)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="deal" id="deal_last_modal"><label class="form-check-label" for="deal_last_modal">Last minute (117)</label></div>
                    </div>

                    <h5 class="filter-title-new">New</h5>
                    <h6 style="text:muted;">Day & Online Experiences</h6>
                    <div style="padding-bottom: 1rem;" class="filter-content">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="new_online"><label class="form-check-label" for="new_online">Online experiences (280)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="new_day"><label class="form-check-label" for="new_day">Day Activities (16)</label></div>
                    </div>
                    
                    <h5 class="filter-title">Destinations</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content filter-accordion">
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDest" role="button" aria-expanded="false" aria-controls="collapseDest">
                        <div class="sb-sidenav-collapse-arrow">The Americas & Caribbean <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseDest">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="radio" id="dest_1"><label class="form-check-label" for="dest_1">Anywhere in Americas & Caribbean</label></div>
                            <div class="form-check">
                                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDestsub" role="button" aria-expanded="false" aria-controls="collapseDestsub">
                                <div class="sb-sidenav-collapse-arrow">USA <i class="fas fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="collapseDestsub">
                                <div class="filter-collapse-body">
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_1"><label class="form-check-label" for="dest_1">Anywhere in USA</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                                </div>
                                </div>
                            </div>
                            <div class="form-check">
                                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDestsub2" role="button" aria-expanded="false" aria-controls="collapseDestsub2">
                                <div class="sb-sidenav-collapse-arrow">Costa Rica <i class="fas fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="collapseDestsub2">
                                <div class="filter-collapse-body">
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_1"><label class="form-check-label" for="dest_1">Anywhere in Costa Rica</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                                </div>
                                </div>
                            </div>
                            <div class="form-check">
                                <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDestsub3" role="button" aria-expanded="false" aria-controls="collapseDestsub3">
                                <div class="sb-sidenav-collapse-arrow">Mexico <i class="fas fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="collapseDestsub3">
                                <div class="filter-collapse-body">
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_1"><label class="form-check-label" for="dest_1">Anywhere in Mexico</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                                </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDest2" role="button" aria-expanded="false" aria-controls="collapseDest2">
                        <div class="sb-sidenav-collapse-arrow">Europe <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseDest2">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_1"><label class="form-check-label" for="dest_1">The Americas & Caribbean</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                        </div>
                        </div>
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDest3" role="button" aria-expanded="false" aria-controls="collapseDest3">
                        <div class="sb-sidenav-collapse-arrow">Asia & Oceania <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseDest3">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_1"><label class="form-check-label" for="dest_1">The Americas & Caribbean</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                        </div>
                        </div>
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseDest4" role="button" aria-expanded="false" aria-controls="collapseDest4">
                        <div class="sb-sidenav-collapse-arrow">Africa & the Middle East <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseDest4">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_1"><label class="form-check-label" for="dest_1">The Americas & Caribbean</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_2"><label class="form-check-label" for="dest_2">Europe</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_3"><label class="form-check-label" for="dest_3">Asia & Oceania</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="dest_4"><label class="form-check-label" for="dest_4">Africa & the Middle East</label></div>
                        </div>
                        </div>
                    </div>
                    
                    <h5 class="filter-title">Arrival date</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                        <div class="form-check"><input class="form-check-input" type="radio" id="date_nov"><label class="form-check-label" for="date_nov">2025 November (4848)</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" id="date_dec"><label class="form-check-label" for="date_dec">2025 December (4340)</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" id="date_jan"><label class="form-check-label" for="date_jan">2026 January (3860)</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" id="date_feb"><label class="form-check-label" for="date_feb">2026 February (3646)</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" id="date_mar"><label class="form-check-label" for="date_mar">2026 March (3813)</label></div>
                        <a href="#" class="filter-show-more">Show more</a>
                    </div>
                    
                    <h5 class="filter-title">Duration</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="duration_static" id="dur_1"><label class="form-check-label" for="dur_1">2 days (93)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="duration_static" id="dur_2"><label class="form-check-label" for="dur_2">From 3 to 7 days (4040)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="duration_static" id="dur_3"><label class="form-check-label" for="dur_3">From 1 to 2 weeks (2786)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="duration_static" id="dur_4"><label class="form-check-label" for="dur_4">More than 2 weeks (1491)</label></div>
                    </div>
                    
                    <h5 class="filter-title">Price per trip</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_1"><label class="form-check-label" for="price_1">Below Rs. 20000 (236)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_2"><label class="form-check-label" for="price_2">From Rs. 20000 to Rs. 50000 (1311)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_3"><label class="form-check-label" for="price_3">From Rs. 50000 to Rs. 80000 (1479)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_4"><label class="form-check-label" for="price_4">From Rs. 80000 to Rs. 150000 (2079)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_5"><label class="form-check-label" for="price_5">From Rs. 150000 to Rs. 300000 (1703)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="price_range" id="price_6"><label class="form-check-label" for="price_6">More than Rs. 300000 (587)</label></div>
                    </div>
                    
                    <h5 class="filter-title">Private</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="private_1"><label class="form-check-label" for="private_1">Private retreats (676)</label></div>
                    </div>
                    
                    <h5 class="filter-title">Language of instruction</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="lang_en"><label class="form-check-label" for="lang_en">English (4979)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="lang_de"><label class="form-check-label" for="lang_de">German (397)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="lang_fr"><label class="form-check-label" for="lang_fr">French (290)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="lang_es"><label class="form-check-label" for="lang_es">Spanish (141)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="lang_nl"><label class="form-check-label" for="lang_nl">Dutch (87)</label></div>
                    </div>
                    
                    <h5 class="filter-title">Meals</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="meal_all"><label class="form-check-label" for="meal_all">All meals included (4238)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="meal_bfast"><label class="form-check-label" for="meal_bfast">Breakfast (5881)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="meal_brunch"><label class="form-check-label" for="meal_brunch">Brunch (693)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="meal_lunch"><label class="form-check-label" for="meal_lunch">Lunch (4654)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="meal_dinner"><label class="form-check-label" for="meal_dinner">Dinner (5614)</label></div>
                    </div>
                    
                    <h5 class="filter-title">Food</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content filter-scroll-box">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                        <a href="#" class="filter-show-more">Show more</a>
                    </div>
                    
                    <h5 class="filter-title">Airport transfer</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="apt_avail"><label class="form-check-label" for="apt_avail">Airport transfer available (2056)</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="apt_incl"><label class="form-check-label" for="apt_incl">Airport transfer included (1766)</label></div>
                    </div>
                    
                    
                    <h5 class="filter-title">Categories</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content filter-accordion">
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat" role="button" aria-expanded="false" aria-controls="collapseCat">
                        <div class="sb-sidenav-collapse-arrow">Budget or luxury <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseCat">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                        </div>
                        </div>
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat2" role="button" aria-expanded="false" aria-controls="collapseCat2">
                        <div class="sb-sidenav-collapse-arrow">Skill Level <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseCat2">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                        </div>
                        </div>
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat3" role="button" aria-expanded="false" aria-controls="collapseCat3">
                        <div class="sb-sidenav-collapse-arrow">Teacher Training <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseCat3">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                        </div>
                        </div>
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat4" role="button" aria-expanded="false" aria-controls="collapseCat4">
                        <div class="sb-sidenav-collapse-arrow">Spirituality & Chanting <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseCat4">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                        </div>
                        </div>
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat5" role="button" aria-expanded="false" aria-controls="collapseCat5">
                        <div class="sb-sidenav-collapse-arrow">Health & Wellness <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseCat5">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                        </div>
                        </div>
                    </div>
                    
                    <h5 class="filter-title">Types</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content filter-accordion">
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat" role="button" aria-expanded="false" aria-controls="collapseCat">
                        <div class="sb-sidenav-collapse-arrow">Sweat & Flow <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseCat">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                        </div>
                        </div>
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat2" role="button" aria-expanded="false" aria-controls="collapseCat2">
                        <div class="sb-sidenav-collapse-arrow">Mindfulness & Meditation <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseCat2">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                        </div>
                        </div>
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat3" role="button" aria-expanded="false" aria-controls="collapseCat3">
                        <div class="sb-sidenav-collapse-arrow">Restore & Revitalize <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseCat3">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                        </div>
                        </div>
                        <a class="filter-collapse-trigger collapsed" data-bs-toggle="collapse" href="#collapseCat4" role="button" aria-expanded="false" aria-controls="collapseCat4">
                        <div class="sb-sidenav-collapse-arrow">Fitness & Strength <i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseCat4">
                        <div class="filter-collapse-body">
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_veg"><label class="form-check-label" for="food_veg">Vegetarian (incl. lacto-ovo) (5599)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_vegan"><label class="form-check-label" for="food_vegan">Vegan (3915)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_gf"><label class="form-check-label" for="food_gf">Gluten-free (2539)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_org"><label class="form-check-label" for="food_org">Organic & whole-foods (2404)</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" id="food_ayu"><label class="form-check-label" for="food_ayu">Ayurvedic & yogic (incl. naturopathic) (2176)</label></div>
                        </div>
                        </div>
                    </div>
                    
                    <h5 class="filter-title">Reviews</h5>
                    <div style="padding-bottom: 1rem;" class="filter-content">
                        <div class="form-check"><input class="form-check-input" type="radio" name="reviews" id="rev_1"><label class="form-check-label" for="rev_1">Excellent (4.5+)</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="reviews" id="rev_2"><label class="form-check-label" for="rev_2">Good (4.0+)</label></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <!-- This button can submit the form *inside* the modal -->
            <button type="button" class="btn btn-primary" onclick="document.getElementById('filterFormModal').submit();">Apply Filters</button>
        </div>
    </div>
</div>
</div>