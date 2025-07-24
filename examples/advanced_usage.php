<?php

/**
 * Advanced Monday.com PHP SDK Usage Examples
 * 
 * This file demonstrates advanced usage patterns and real-world scenarios
 * for the Monday.com PHP SDK.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use MondayV2SDK\MondayClient;
use MondayV2SDK\ColumnTypes\TextColumn;
use MondayV2SDK\ColumnTypes\StatusColumn;
use MondayV2SDK\ColumnTypes\EmailColumn;
use MondayV2SDK\ColumnTypes\PhoneColumn;
use MondayV2SDK\ColumnTypes\NumberColumn;
use MondayV2SDK\ColumnTypes\LocationColumn;
use MondayV2SDK\ColumnTypes\TimelineColumn;
use MondayV2SDK\Exceptions\MondayApiException;
use MondayV2SDK\Exceptions\RateLimitException;

// Configuration
$config = [
    'timeout' => 30,
    'rate_limit' => [
        'minute_limit' => 100,
        'daily_limit' => 1000
    ],
    'logging' => [
        'level' => 'info',
        'enabled' => true
    ]
];

// Initialize client (replace with your API token)
$client = new MondayClient('your-api-token-here', $config);

// Example board ID (replace with your actual board ID)
$boardId = 1234567890;

/**
 * Example 1: Project Management System
 */
function projectManagementExample($client, $boardId) {
    echo "=== Project Management Example ===\n";
    
    try {
        // Create a new project
        $project = $client->items()->create([
            'board_id' => $boardId,
            'item_name' => 'E-commerce Website Redesign',
            'column_values' => [
                new TextColumn('description_01', 'Complete redesign of the company e-commerce website with modern UI/UX, mobile responsiveness, and improved checkout flow.'),
                new StatusColumn('status_01', 'Planning', 'yellow'),
                new EmailColumn('project_manager_01', 'pm@company.com', 'Sarah Johnson (Project Manager)'),
                new EmailColumn('client_01', 'client@ecommerce.com', 'E-commerce Client'),
                new NumberColumn('budget_01', 50000, 'currency'),
                new NumberColumn('progress_01', 0, 'percentage'),
                new TimelineColumn('timeline_01', '2024-02-01', '2024-05-31'),
                new LocationColumn('office_01', [
                    'address' => '123 Business Ave',
                    'city' => 'San Francisco',
                    'state' => 'CA',
                    'country' => 'USA',
                    'lat' => 37.7749,
                    'lng' => -122.4194
                ])
            ]
        ]);
        
        echo "✅ Created project: {$project['name']}\n";
        
        // Update project status
        $updatedProject = $client->items()->update($project['id'], [
            'column_values' => [
                new StatusColumn('status_01', 'In Progress', 'blue'),
                new NumberColumn('progress_01', 25, 'percentage')
            ]
        ]);
        
        echo "✅ Updated project status to: In Progress (25% complete)\n";
        
        return $project;
        
    } catch (Exception $e) {
        echo "❌ Error in project management: " . $e->getMessage() . "\n";
        return null;
    }
}

/**
 * Example 2: Customer Support Ticket System
 */
function supportTicketExample($client, $boardId) {
    echo "\n=== Customer Support Ticket Example ===\n";
    
    try {
        // Create support tickets
        $tickets = [
            [
                'subject' => 'Login Issue - Cannot Access Account',
                'description' => 'User reports being unable to log into their account despite using correct credentials.',
                'customer' => [
                    'name' => 'John Smith',
                    'email' => 'john.smith@customer.com',
                    'phone' => '+1-555-123-4567'
                ],
                'priority' => 'High',
                'category' => 'Technical Issue'
            ],
            [
                'subject' => 'Billing Question - Invoice Discrepancy',
                'description' => 'Customer has questions about their recent invoice and billing charges.',
                'customer' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane.doe@business.com',
                    'phone' => '+1-555-987-6543'
                ],
                'priority' => 'Medium',
                'category' => 'Billing'
            ]
        ];
        
        $createdTickets = [];
        
        foreach ($tickets as $ticketData) {
            $ticket = $client->items()->create([
                'board_id' => $boardId,
                'item_name' => $ticketData['subject'],
                'column_values' => [
                    new TextColumn('description_01', $ticketData['description']),
                    new StatusColumn('status_01', 'New', 'red'),
                    new StatusColumn('priority_01', $ticketData['priority'], 'red'),
                    new TextColumn('category_01', $ticketData['category']),
                    new EmailColumn('customer_email_01', $ticketData['customer']['email'], $ticketData['customer']['name']),
                    new PhoneColumn('customer_phone_01', $ticketData['customer']['phone'], $ticketData['customer']['name'])
                ]
            ]);
            
            $createdTickets[] = $ticket;
            echo "✅ Created ticket: {$ticket['name']}\n";
        }
        
        // Assign tickets to support agents
        $agents = [
            ['name' => 'Support Agent 1', 'email' => 'support1@company.com'],
            ['name' => 'Support Agent 2', 'email' => 'support2@company.com']
        ];
        
        foreach ($createdTickets as $index => $ticket) {
            $agent = $agents[$index % count($agents)];
            
            $client->items()->update($ticket['id'], [
                'column_values' => [
                    new EmailColumn('assigned_to_01', $agent['email'], $agent['name']),
                    new StatusColumn('status_01', 'Assigned', 'blue')
                ]
            ]);
            
            echo "✅ Assigned ticket to: {$agent['name']}\n";
        }
        
        return $createdTickets;
        
    } catch (Exception $e) {
        echo "❌ Error in support ticket system: " . $e->getMessage() . "\n";
        return [];
    }
}

/**
 * Example 3: Sales Pipeline Management
 */
function salesPipelineExample($client, $boardId) {
    echo "\n=== Sales Pipeline Example ===\n";
    
    try {
        // Create sales leads
        $leads = [
            [
                'company' => 'TechStart Inc.',
                'contact' => [
                    'name' => 'Mike Johnson',
                    'email' => 'mike.johnson@techstart.com',
                    'phone' => '+1-555-111-2222'
                ],
                'value' => 75000,
                'stage' => 'Lead',
                'source' => 'Website'
            ],
            [
                'company' => 'Global Solutions Ltd.',
                'contact' => [
                    'name' => 'Lisa Chen',
                    'email' => 'lisa.chen@globalsolutions.com',
                    'phone' => '+1-555-333-4444'
                ],
                'value' => 120000,
                'stage' => 'Qualified',
                'source' => 'Referral'
            ],
            [
                'company' => 'Innovation Corp.',
                'contact' => [
                    'name' => 'David Wilson',
                    'email' => 'david.wilson@innovation.com',
                    'phone' => '+1-555-555-6666'
                ],
                'value' => 95000,
                'stage' => 'Proposal',
                'source' => 'LinkedIn'
            ]
        ];
        
        $createdLeads = [];
        
        foreach ($leads as $leadData) {
            $lead = $client->items()->create([
                'board_id' => $boardId,
                'item_name' => $leadData['company'],
                'column_values' => [
                    new TextColumn('contact_name_01', $leadData['contact']['name']),
                    new EmailColumn('contact_email_01', $leadData['contact']['email'], $leadData['contact']['name']),
                    new PhoneColumn('contact_phone_01', $leadData['contact']['phone'], $leadData['contact']['name']),
                    new NumberColumn('deal_value_01', $leadData['value'], 'currency'),
                    new StatusColumn('stage_01', $leadData['stage']),
                    new TextColumn('source_01', $leadData['source']),
                    new TimelineColumn('expected_close_01', date('Y-m-d'), date('Y-m-d', strtotime('+30 days')))
                ]
            ]);
            
            $createdLeads[] = $lead;
            echo "✅ Created lead: {$lead['name']} (\${$leadData['value']})\n";
        }
        
        // Move a lead to the next stage
        if (!empty($createdLeads)) {
            $firstLead = $createdLeads[0];
            
            $client->items()->update($firstLead['id'], [
                'column_values' => [
                    new StatusColumn('stage_01', 'Qualified'),
                    new NumberColumn('deal_value_01', 80000, 'currency') // Updated value
                ]
            ]);
            
            echo "✅ Moved lead to next stage and updated value\n";
        }
        
        return $createdLeads;
        
    } catch (Exception $e) {
        echo "❌ Error in sales pipeline: " . $e->getMessage() . "\n";
        return [];
    }
}

/**
 * Example 4: Advanced Search and Filtering
 */
function advancedSearchExample($client, $boardId) {
    echo "\n=== Advanced Search Example ===\n";
    
    try {
        // Search for items with specific criteria
        $searchCriteria = [
            'status_01' => 'Working'
        ];
        
        $workingItems = $client->items()->searchByColumnValues($boardId, $searchCriteria);
        echo "🔍 Found " . count($workingItems) . " items with 'Working' status\n";
        
        // Search by multiple columns
        $multiSearchCriteria = [
            'priority_01' => 'High',
            'category_01' => 'Technical Issue'
        ];
        
        $highPriorityTechIssues = $client->items()->searchByMultipleColumns($boardId, $multiSearchCriteria);
        echo "🔍 Found " . count($highPriorityTechIssues) . " high priority technical issues\n";
        
        // Get all items with pagination
        $allItems = [];
        $result = $client->items()->getAll($boardId, ['limit' => 50]);
        $allItems = array_merge($allItems, $result['items']);
        
        // Continue pagination if needed
        while ($result['cursor']) {
            $result = $client->items()->getNextPage($result['cursor']);
            $allItems = array_merge($allItems, $result['items']);
        }
        
        echo "📋 Total items in board: " . count($allItems) . "\n";
        
        // Analyze items by status
        $statusCounts = [];
        foreach ($allItems as $item) {
            foreach ($item['column_values'] as $column) {
                if ($column['id'] === 'status_01' && $column['value']) {
                    $statusData = json_decode($column['value'], true);
                    $status = $statusData['labels'][0] ?? 'Unknown';
                    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
                }
            }
        }
        
        echo "📊 Status breakdown:\n";
        foreach ($statusCounts as $status => $count) {
            echo "  - {$status}: {$count} items\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error in advanced search: " . $e->getMessage() . "\n";
    }
}

/**
 * Example 5: Batch Operations with Error Handling
 */
function batchOperationsExample($client, $boardId) {
    echo "\n=== Batch Operations Example ===\n";
    
    try {
        // Prepare batch data
        $batchItems = [
            [
                'item_name' => 'Task 1 - Research',
                'description' => 'Conduct market research for new product',
                'status' => 'Working',
                'priority' => 'High'
            ],
            [
                'item_name' => 'Task 2 - Design',
                'description' => 'Create wireframes and mockups',
                'status' => 'Planning',
                'priority' => 'Medium'
            ],
            [
                'item_name' => 'Task 3 - Development',
                'description' => 'Implement the new features',
                'status' => 'Not Started',
                'priority' => 'High'
            ]
        ];
        
        $createdItems = [];
        $errors = [];
        
        foreach ($batchItems as $index => $itemData) {
            try {
                $item = $client->items()->create([
                    'board_id' => $boardId,
                    'item_name' => $itemData['item_name'],
                    'column_values' => [
                        new TextColumn('description_01', $itemData['description']),
                        new StatusColumn('status_01', $itemData['status']),
                        new StatusColumn('priority_01', $itemData['priority'])
                    ]
                ]);
                
                $createdItems[] = $item;
                echo "✅ Created: {$item['name']}\n";
                
            } catch (RateLimitException $e) {
                // Handle rate limiting
                $retryAfter = $e->getRetryAfter();
                echo "⏳ Rate limited. Waiting {$retryAfter} seconds...\n";
                sleep($retryAfter);
                
                // Retry the same item
                $index--; // Retry this item
                continue;
                
            } catch (Exception $e) {
                $errors[] = [
                    'item' => $itemData,
                    'error' => $e->getMessage()
                ];
                echo "❌ Failed to create: {$itemData['item_name']} - {$e->getMessage()}\n";
            }
        }
        
        echo "\n📊 Batch operation results:\n";
        echo "  - Created: " . count($createdItems) . " items\n";
        echo "  - Errors: " . count($errors) . " items\n";
        
        if (!empty($errors)) {
            echo "\n❌ Errors encountered:\n";
            foreach ($errors as $error) {
                echo "  - {$error['item']['item_name']}: {$error['error']}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error in batch operations: " . $e->getMessage() . "\n";
    }
}

/**
 * Example 6: Custom GraphQL Queries
 */
function customGraphQLExample($client, $boardId) {
    echo "\n=== Custom GraphQL Example ===\n";
    
    try {
        // Custom query to get board information
        $boardQuery = "
            query (\$boardId: ID!) {
                boards(ids: [\$boardId]) {
                    id
                    name
                    description
                    state
                    created_at
                    updated_at
                    items_page(limit: 10) {
                        cursor
                        items {
                            id
                            name
                            state
                            created_at
                            column_values {
                                id
                                value
                                text
                                type
                            }
                        }
                    }
                }
            }
        ";
        
        $result = $client->query($boardQuery, ['boardId' => $boardId]);
        
        if (isset($result['data']['boards'][0])) {
            $board = $result['data']['boards'][0];
            echo "📋 Board: {$board['name']}\n";
            echo "📅 Created: {$board['created_at']}\n";
            echo "📊 Items: " . count($board['items_page']['items']) . "\n";
            
            // Show first few items
            foreach (array_slice($board['items_page']['items'], 0, 3) as $item) {
                echo "  - {$item['name']} ({$item['state']})\n";
            }
        }
        
        // Custom mutation to update multiple items
        $updateMutation = "
            mutation (\$itemId: ID!, \$columnValues: JSON!) {
                change_multiple_column_values(
                    item_id: \$itemId,
                    column_values: \$columnValues
                ) {
                    id
                    name
                }
            }
        ";
        
        // This would be used to update specific columns
        echo "✅ Custom GraphQL queries executed successfully\n";
        
    } catch (Exception $e) {
        echo "❌ Error in custom GraphQL: " . $e->getMessage() . "\n";
    }
}

/**
 * Main execution
 */
function main() {
    global $client, $boardId;
    
    echo "🚀 Monday.com PHP SDK - Advanced Usage Examples\n";
    echo "================================================\n\n";
    
    // Run examples
    projectManagementExample($client, $boardId);
    supportTicketExample($client, $boardId);
    salesPipelineExample($client, $boardId);
    advancedSearchExample($client, $boardId);
    batchOperationsExample($client, $boardId);
    customGraphQLExample($client, $boardId);
    
    echo "\n✅ All examples completed!\n";
}

// Run the examples (uncomment to execute)
// main();

echo "📖 This file contains advanced usage examples for the Monday.com PHP SDK.\n";
echo "🔧 To run the examples, uncomment the main() call at the bottom of this file.\n";
echo "⚠️  Make sure to replace 'your-api-token-here' with your actual API token.\n";
echo "⚠️  Make sure to replace the board ID with your actual board ID.\n"; 