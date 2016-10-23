def createWolfpack(agents, search_space)
    dimensions = search_space[0].length
    wolfpack = Array::new(agents) { Array::new(dimensions) }

    agents.times { |wolf|
        dimensions.times { |dimension|
            range = search_space[1][dimension] - search_space[0][dimension]
            wolfpack[wolf][dimension] = search_space[0][dimension] + rand() * range
        }
    }

    return wolfpack
end

def checkWolfpack(wolfpack, search_space)
    agents = wolfpack.length
    dimensions = wolfpack[0].length

    agents.times { |wolf|
        dimensions.times { |dimension|
            if wolfpack[wolf][dimension] < search_space[0][dimension] || wolfpack[wolf][dimension] > search_space[1][dimension]
                range = search_space[1][dimension] - search_space[0][dimension]
                wolfpack[wolf][dimension] = search_space[0][dimension] + rand() * range
            end
        }
    }
    return wolfpack
end

def updateWolfpack(leaders, wolfpack, a)
    agents = wolfpack.length
    dimensions = wolfpack[0].length

    agents.times { |wolf|
        dimensions.times { |dimension|
            x = Array::new(3, 0)
            leaders.each { |leader|
                a1 = 2 * a * rand() - a
                c1 = 2 * rand();
                dist = (c1 * leader[dimension] - wolfpack[wolf][dimension]).abs
                x.push(leader[dimension] - (a1 * dist))
            }
            wolfpack[wolf][dimension] = x.inject{ |sum, i| sum + i }.to_f / x.size
        }
    }
    return wolfpack
end

def gwo(agents, iterations, search_space)
    alpha_position = beta_position  = delta_position = nil
    alpha_score = beta_score = delta_score = Float::INFINITY

    wolfpack = createWolfpack(agents, search_space)

    fitness = Array::new(agents)
    iterations.times { |i|
        # Return back the search agents that go beyond the bonduries of the search space
        wolfpack = checkWolfpack(wolfpack, search_space)

        # Calculate objective function for each search agent
        agents.times { |j|
            # Sphere function
            fitness[j] =  wolfpack[j].inject(0.0){ |sum, d| sum + (d ** 2) }

            # Update Alpha, Beta and Delta
            if (fitness[j] < alpha_score)
                alpha_score = fitness[j];
                alpha_position = wolfpack[j];
            end

            if (fitness[j] > alpha_score && fitness[j] < beta_score)
                beta_score = fitness[j]
                beta_position = wolfpack[j]
            end

            if (fitness[j] > alpha_score && fitness[j] > beta_score && fitness[j] < delta_score)
                delta_score = fitness[j]
                delta_position = wolfpack[j]
            end
        }

        a = 2 - i * (2.0 / iterations)

        # Update the positions of search agents with respect to leader
        leaders = [alpha_position, beta_position, delta_position]
        wolfpack = updateWolfpack(leaders, wolfpack, a)

        puts "Iteration #{i}: Best Fitness #{alpha_score}\n"
    }
end

gwo(100, 100, [Array::new(30, -100.0), Array::new(30, 100.0)]);
