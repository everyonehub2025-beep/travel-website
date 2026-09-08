<?php
/**
 * SDI Travel Trust — DEMO DATA seeder for members, points, and referrals.
 *
 * WHY THIS FILE EXISTS SEPARATELY FROM sdi-demo-content.xml:
 * WordPress's standard WXR import format (the .xml file) can create posts,
 * pages, menus, and taxonomy terms — but it cannot create real WordPress
 * user accounts (no password data in WXR) or rows in this plugin's custom
 * `sdi_points_ledger` / `sdi_referrals` database tables (WXR only knows
 * about posts). The brief's requirement for "5 dummy members at varying
 * point totals" and "15-20 dummy referrals" therefore needs a small PHP
 * script that runs inside WordPress and calls the plugin's own APIs —
 * this file. It reuses SDI_Points / SDI_Referrals exactly as the live
 * plugin does, so the demo data is exercised through the same code path
 * a real site uses.
 *
 * THIS IS DEMO DATA. Every account/email below is clearly fake
 * (@example.com) and prefixed `sdi_demo_`. Delete these accounts and
 * their ledger/referral rows before launch — see SETUP.md and
 * CLIENT-HANDOVER.md for the removal steps.
 *
 * HOW TO RUN (requires WP-CLI and both plugins/theme already active):
 *   wp eval-file sdi-demo-content-seed.php
 *
 * Safe to run only once — it checks the `sdi_demo_seeded` option and
 * exits early if demo data already exists.
 *
 * NOT EXECUTED OR TESTED AGAINST A LIVE INSTALL — see SETUP.md.
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo "This script must be run inside WordPress via WP-CLI:\n";
	echo "  wp eval-file sdi-demo-content-seed.php\n";
	exit( 1 );
}

if ( get_option( 'sdi_demo_seeded' ) ) {
	echo "Demo data already seeded (option 'sdi_demo_seeded' is set). Nothing to do.\n";
	echo "To re-seed, first delete the sdi_demo_* users and clear the sdi_points_ledger / sdi_referrals rows, then delete_option( 'sdi_demo_seeded' ).\n";
	exit( 0 );
}

if ( ! class_exists( 'SDI_Points' ) || ! class_exists( 'SDI_Referrals' ) ) {
	echo "SDI Trust Core plugin is not active. Activate it first, then re-run this script.\n";
	exit( 1 );
}

/**
 * Create (or fetch, if already present) a demo subscriber account.
 *
 * @param string $username Login name.
 * @param string $display  Display name.
 * @param string $email    Email address.
 * @return int User ID.
 */
function sdi_demo_get_or_create_user( $username, $display, $email ) {
	$existing = get_user_by( 'login', $username );
	if ( $existing ) {
		return $existing->ID;
	}

	$user_id = wp_insert_user(
		array(
			'user_login'   => $username,
			'user_pass'    => wp_generate_password( 20 ),
			'user_email'   => $email,
			'display_name' => $display,
			'role'         => 'subscriber',
		)
	);

	if ( is_wp_error( $user_id ) ) {
		echo 'Could not create user ' . esc_html( $username ) . ': ' . esc_html( $user_id->get_error_message() ) . "\n";
		return 0;
	}

	return $user_id;
}

echo "Seeding SDI Travel Trust demo content...\n\n";

// ---------------------------------------------------------------------
// 1. Five members, one per tier state: 0 / 300 / 600 / 900 / 1,200.
// ---------------------------------------------------------------------

$tier_members = array(
	array( 'sdi_demo_member_zero', 'Jordan Ellis (Demo — 0 pts)', 'sdi.demo.zero@example.com', 0 ),
	array( 'sdi_demo_member_300', 'Priya Nataraj (Demo — 300 pts)', 'sdi.demo.tier300@example.com', 300 ),
	array( 'sdi_demo_member_600', 'Marcus Webb (Demo — 600 pts)', 'sdi.demo.tier600@example.com', 600 ),
	array( 'sdi_demo_member_900', 'Renee Castillo (Demo — 900 pts)', 'sdi.demo.tier900@example.com', 900 ),
	array( 'sdi_demo_member_1200', 'Aiden Cho (Demo — 1,200 pts)', 'sdi.demo.tier1200@example.com', 1200 ),
);

$tier_member_ids = array();

foreach ( $tier_members as $m ) {
	list( $username, $display, $email, $target_balance ) = $m;
	$user_id = sdi_demo_get_or_create_user( $username, $display, $email );
	if ( ! $user_id ) {
		continue;
	}
	$tier_member_ids[ $target_balance ] = $user_id;

	if ( $target_balance > 0 ) {
		SDI_Points::add_entry(
			$user_id,
			$target_balance,
			'manual_adjust',
			null,
			'Demo data seed — initial balance for tier-state testing.',
			1
		);
	}

	echo "Member: {$display} -> user_id {$user_id}, balance {$target_balance}\n";
}

// One harmless pending referral per tier member (doesn't affect balance).
foreach ( $tier_member_ids as $balance => $user_id ) {
	SDI_Referrals::submit(
		$user_id,
		'Demo Referral ' . $balance,
		'sdi.demo.referral.' . $balance . '@example.com',
		''
	);
}

// ---------------------------------------------------------------------
// 2. Three extra "power referrer" accounts to exercise every referral
//    status (pending / approved / rejected / duplicate) realistically,
//    without disturbing the exact tier balances seeded above.
// ---------------------------------------------------------------------

$referrers = array(
	sdi_demo_get_or_create_user( 'sdi_demo_referrer_1', 'Dana Whitfield (Demo)', 'sdi.demo.referrer1@example.com' ),
	sdi_demo_get_or_create_user( 'sdi_demo_referrer_2', 'Sam Okafor (Demo)', 'sdi.demo.referrer2@example.com' ),
	sdi_demo_get_or_create_user( 'sdi_demo_referrer_3', 'Lena Petrova (Demo)', 'sdi.demo.referrer3@example.com' ),
);
$referrers = array_values( array_filter( $referrers ) );

$demo_referral_names = array(
	'Alex Rivera', 'Taylor Brooks', 'Morgan Lee', 'Jamie Chen', 'Casey Morgan',
	'Robin Patel', 'Drew Simmons', 'Skyler Hughes', 'Jordan Blake', 'Avery Coleman',
	'Reese Anand', 'Quinn Delgado', 'Rowan Foster',
);

$referral_ids = array();
$idx          = 0;

foreach ( $referrers as $referrer_id ) {
	for ( $i = 0; $i < 5 && $idx < count( $demo_referral_names ); $i++, $idx++ ) {
		$name  = $demo_referral_names[ $idx ];
		$email = 'sdi.demo.' . sanitize_title( $name ) . '@example.com';
		$result = SDI_Referrals::submit( $referrer_id, $name, $email, '' );
		if ( ! is_wp_error( $result ) ) {
			$referral_ids[] = $result;
		}
	}
}

// Approve the first ~5, reject the next ~4, leave the rest pending —
// duplicates are already created automatically by re-submitting an
// existing referred email below.
$to_approve = array_slice( $referral_ids, 0, 5 );
$to_reject  = array_slice( $referral_ids, 5, 4 );

foreach ( $to_approve as $rid ) {
	SDI_Referrals::approve( $rid, 1, 'Demo data seed — approved for testing.' );
}
foreach ( $to_reject as $rid ) {
	SDI_Referrals::reject( $rid, 1, 'Demo data seed — rejected for testing (duplicate submission elsewhere).' );
}

// A couple of intentional duplicates.
if ( ! empty( $referrers[0] ) && ! empty( $demo_referral_names ) ) {
	SDI_Referrals::submit( $referrers[0], $demo_referral_names[0], 'sdi.demo.' . sanitize_title( $demo_referral_names[0] ) . '@example.com', '' );
}
if ( ! empty( $referrers[1] ) && count( $demo_referral_names ) > 5 ) {
	SDI_Referrals::submit( $referrers[1], $demo_referral_names[5], 'sdi.demo.' . sanitize_title( $demo_referral_names[5] ) . '@example.com', '' );
}

update_option( 'sdi_demo_seeded', gmdate( 'Y-m-d H:i:s' ) );

echo "\nDone. Created/verified " . ( count( $tier_member_ids ) + count( $referrers ) ) . " demo member accounts ";
echo "and " . ( count( $referral_ids ) + count( $tier_member_ids ) + 2 ) . " referral rows.\n";
echo "All demo accounts use @example.com addresses and the sdi_demo_ username prefix — see CLIENT-HANDOVER.md to remove them before launch.\n";
